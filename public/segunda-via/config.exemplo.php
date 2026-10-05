<?php

declare(strict_types=1);

/*
 * Configuração do reCAPTCHA v3 — chaves do Google Cloud.
 *
 * Como obter as chaves:
 * 1. Acesse https://console.cloud.google.com/security/recaptcha
 * 2. Crie uma chave WEB do tipo "reCAPTCHA v3" (score) para os domínios
 *    do site (ex.: cordeiroenergia.com.br e *.pages.dev).
 * 3. Copie a CHAVE DO SITE para o arquivo .env na raiz do projeto:
 *       PUBLIC_RECAPTCHA_SITE_KEY=sua_chave_do_site
 *    (o pop-up do site usa essa chave; rebuild obrigatório: npm run build)
 * 4. Copie a CHAVE SECRETA apenas para o campo abaixo de um arquivo
 *       public/segunda-via/config.php   (NUNCA versione nem exponha no front).
 * 5. Envie o config.php junto com a pasta public/segunda-via para a HostGator.
 *
 * Com os dois campos vazios, a verificação fica DESATIVADA
 * (comportamento atual em desenvolvimento local).
 *
 * Limite gratuito: 10.000 verificações/mês no Google Cloud.
 */

return [
    'recaptcha_site_key' => '',
    'recaptcha_secret' => '',
];
