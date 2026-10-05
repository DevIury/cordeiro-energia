<?php

declare(strict_types=1);

require_once __DIR__ . '/boleto.php';

$config = [];
$cfgArquivo = __DIR__ . '/config.php';
if (is_file($cfgArquivo)) {
    $cfgFonte = (string) file_get_contents($cfgArquivo);
    if (strncmp($cfgFonte, "\xEF\xBB\xBF", 3) === 0) {
        file_put_contents($cfgArquivo, substr($cfgFonte, 3));
    }
    $config = require $cfgArquivo;
}

/**
 * Verifica o token do reCAPTCHA v3 no Google (siteverify).
 * Retorna null quando a verificação passa; uma mensagem de erro, caso contrário.
 * Só é chamada quando uma secret key está configurada em config.php.
 */
function verificarRecaptcha(string $secret, mixed $token): ?string
{
    if (!is_string($token) || $token === '') {
        return 'Validação de segurança ausente. Recarregue a página e tente novamente.';
    }

    $corpo = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $contexto = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
            'content' => $corpo,
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]);
    $resposta = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $contexto);
    if ($resposta === false) {
        error_log('reCAPTCHA: sem conexao com siteverify');
        return 'Não foi possível verificar a segurança da página. Tente novamente em instantes.';
    }

    $dados = json_decode($resposta, true);
    if (!is_array($dados) || ($dados['success'] ?? false) !== true) {
        error_log('reCAPTCHA rejeitou o token: ' . substr($resposta, 0, 300));
        return 'Validação anti-robô reprovada. Tente novamente.';
    }

    $score = (float) ($dados['score'] ?? 0);
    $acao = (string) ($dados['action'] ?? '');
    if ($acao !== 'segunda_via' || $score < 0.5) {
        error_log(sprintf('reCAPTCHA score/ação insuficientes: action=%s score=%.2f', $acao, $score));
        return 'Validação anti-robô reprovada. Tente novamente.';
    }

    return null;
}

$erro = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $secret = trim((string) ($config['recaptcha_secret'] ?? ''));
    $erro = $secret !== ''
        ? verificarRecaptcha($secret, $_POST['g-recaptcha-response'] ?? '')
        : null;

    if ($erro === null) {
        $documento = trim((string) ($_POST['documento'] ?? ''));
        $somenteDigitos = preg_replace('/\D+/', '', $documento) ?? '';

        if ($somenteDigitos === '') {
            $erro = 'Informe o CPF ou o código do cliente.';
        } elseif (strlen($somenteDigitos) > 14) {
            $erro = 'Documento inválido. Verifique os dados informados.';
        } else {
            $cliente = clientePorDocumento($somenteDigitos);
            if ($cliente === null) {
                $erro = 'Cliente não localizado. Para testar, use 123.456.789-09 ou 529.982.247-25.';
            } else {
                $boleto = montarBoleto($cliente);
                $pdf = gerarPdfBoleto($boleto);
                $nomeArquivo = 'segunda-via-' . $somenteDigitos . '-' . $boleto['cliente']['vencimento'] . '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
                header('Content-Length: ' . strlen($pdf));
                header('Cache-Control: no-store');
                header('X-Content-Type-Options: nosniff');
                echo $pdf;
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Segunda via de boleto — Cordeiro Energia</title>
<style>
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter/Inter-Regular.woff2') format('woff2');
  font-weight: 400;
  font-display: swap;
}
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter/Inter-SemiBold.woff2') format('woff2');
  font-weight: 600;
  font-display: swap;
}
@font-face {
  font-family: 'Inter';
  src: url('/fonts/inter/Inter-Bold.woff2') format('woff2');
  font-weight: 700;
  font-display: swap;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Inter', system-ui, sans-serif;
  background: #0a0a0a;
  color: #e5e5e5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.topo {
  background: #111;
  border-bottom: 1px solid #262626;
  padding: 18px 20px;
}
.topo-inner, .conteudo, .rodape-inner {
  max-width: 560px;
  margin: 0 auto;
  width: 100%;
}
.marca {
  font-weight: 700;
  font-size: 18px;
  color: #FE5C17;
  letter-spacing: .04em;
}
.marca span { color: #e5e5e5; font-weight: 400; }
.conteudo {
  flex: 1;
  padding: 40px 20px;
}
h1 {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 8px;
}
.sub {
  color: #a3a3a3;
  font-size: 14px;
  line-height: 1.6;
  margin-bottom: 28px;
}
.cartao {
  background: #141414;
  border: 1px solid #262626;
  border-radius: 12px;
  padding: 24px;
}
label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #d4d4d4;
}
input {
  width: 100%;
  background: #0a0a0a;
  border: 1px solid #333;
  border-radius: 8px;
  color: #fff;
  font: inherit;
  font-size: 16px;
  padding: 13px 14px;
}
input:focus {
  outline: 2px solid #FE5C17;
  outline-offset: 1px;
  border-color: transparent;
}
input::placeholder { color: #666; }
.botao {
  margin-top: 16px;
  width: 100%;
  border: 0;
  border-radius: 8px;
  background: linear-gradient(90deg, #FF8F00, #FE5C17);
  color: #fff;
  font: inherit;
  font-weight: 700;
  font-size: 15px;
  padding: 14px;
  cursor: pointer;
}
.botao:hover { filter: brightness(1.06); }
.erro {
  margin-top: 16px;
  background: rgba(239, 68, 68, .12);
  border: 1px solid rgba(239, 68, 68, .4);
  color: #fca5a5;
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 14px;
  line-height: 1.5;
}
.dica {
  margin-top: 18px;
  font-size: 13px;
  color: #8a8a8a;
  line-height: 1.7;
}
.dica code {
  background: #0a0a0a;
  border: 1px solid #333;
  border-radius: 4px;
  padding: 1px 6px;
  color: #FFC107;
  font-size: 12px;
}
.aviso {
  margin-top: 24px;
  border-left: 3px solid #FE5C17;
  padding: 10px 14px;
  background: #111;
  border-radius: 0 8px 8px 0;
  font-size: 13px;
  color: #a3a3a3;
  line-height: 1.6;
}
.rodape {
  border-top: 1px solid #1f1f1f;
  padding: 20px;
  font-size: 13px;
  color: #737373;
}
.rodape a { color: #FE5C17; text-decoration: none; }
.rodape a:hover { text-decoration: underline; }
</style>
</head>
<body>
<header class="topo">
  <div class="topo-inner">
    <div class="marca">CORDEIRO ENERGIA <span>/ Segunda via</span></div>
  </div>
</header>

<main class="conteudo">
  <h1>Segunda via de boleto</h1>
  <p class="sub">Informe o CPF ou o código do cliente para gerar uma nova via em PDF com linha digitável, código de barras e QR Code PIX (fictício).</p>

  <div class="cartao">
    <form method="post" action="/segunda-via/" id="formSegundaVia" novalidate>
      <label for="documento">CPF ou código do cliente</label>
      <input
        type="text"
        id="documento"
        name="documento"
        value="<?= htmlspecialchars($_POST['documento'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
        placeholder="000.000.000-00"
        inputmode="numeric"
        maxlength="20"
        autocomplete="off"
        autofocus
        required
      >
      <input type="hidden" name="g-recaptcha-response" id="recaptchaToken">
      <button class="botao" type="submit">Gerar PDF para download</button>
      <p class="erro" id="erroJs" role="alert" hidden></p>
      <?php if ($erro !== null): ?>
        <p class="erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </form>

    <p class="dica">
      Dados de teste:<br>
      <code>123.456.789-09</code> — Maria (R$ 189,90)<br>
      <code>529.982.247-25</code> — João (R$ 459,00)
    </p>
  </div>

  <p class="aviso">
    Demonstração técnica: os dados são fictícios, nada é armazenado e o documento
    gerado não pode ser pago.
  </p>
</main>

<footer class="rodape">
  <div class="rodape-inner">
    <a href="/">← Voltar ao site</a>
  </div>
</footer>
<?php $siteKey = trim((string) ($config['recaptcha_site_key'] ?? '')); ?>
<?php if ($siteKey !== ''): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>" async defer></script>
<script>
(function () {
  var chave = <?= json_encode($siteKey, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  var form = document.getElementById('formSegundaVia');
  var token = document.getElementById('recaptchaToken');
  var botao = form.querySelector('button[type="submit"]');
  var erroJs = document.getElementById('erroJs');
  var pronto = false;

  function falha(msg) {
    erroJs.textContent = msg;
    erroJs.hidden = false;
    botao.disabled = false;
    botao.textContent = 'Gerar PDF para download';
  }

  form.addEventListener('submit', function (ev) {
    if (pronto) return;
    ev.preventDefault();
    if (!window.grecaptcha || typeof window.grecaptcha.ready !== 'function') {
      falha('O reCAPTCHA não carregou. Verifique sua conexão e recarregue a página.');
      return;
    }
    erroJs.hidden = true;
    botao.disabled = true;
    botao.textContent = 'Verificando segurança...';
    window.grecaptcha.ready(function () {
      window.grecaptcha.execute(chave, { action: 'segunda_via' }).then(function (t) {
        token.value = t;
        pronto = true;
        botao.textContent = 'Gerando PDF...';
        form.submit();
      }).catch(function () {
        falha('Falha ao verificar a segurança. Tente novamente.');
      });
    });
  });

  window.addEventListener('pageshow', function () {
    pronto = false;
    token.value = '';
    botao.disabled = false;
    botao.textContent = 'Gerar PDF para download';
  });
})();
</script>
<?php endif; ?>
</body>
</html>
