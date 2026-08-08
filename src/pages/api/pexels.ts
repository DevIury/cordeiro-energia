export async function GET({ url }: { url: URL }) {
  const query = url.searchParams.get('q');
  const perPage = url.searchParams.get('per_page') || '10';

  if (!query) {
    return new Response(JSON.stringify({ error: 'Query parameter "q" is required' }), {
      status: 400,
      headers: { 'Content-Type': 'application/json' },
    });
  }

  const apiKey = import.meta.env.PEXELS_API_KEY;

  if (!apiKey || apiKey === 'your_key_here') {
    return new Response(JSON.stringify({ error: 'Pexels API key not configured' }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' },
    });
  }

  try {
    const response = await fetch(
      `https://api.pexels.com/v1/search?query=${encodeURIComponent(query)}&per_page=${perPage}`,
      {
        headers: {
          Authorization: apiKey,
        },
      }
    );

    const data = await response.json();

    return new Response(JSON.stringify(data), {
      status: 200,
      headers: { 'Content-Type': 'application/json' },
    });
  } catch {
    return new Response(JSON.stringify({ error: 'Failed to fetch from Pexels API' }), {
      status: 500,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}
