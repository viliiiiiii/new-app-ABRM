document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('global-search');
    if (search) {
        search.addEventListener('change', async () => {
            const query = search.value.trim();
            if (!query) return;
            const response = await fetch(`/api/index.php?resource=lost-and-found&q=${encodeURIComponent(query)}`);
            const json = await response.json();
            console.log('Search results', json);
        });
    }
});
