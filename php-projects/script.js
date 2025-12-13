// script.js

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchInput');
    const table = document.getElementById('itemsTable');
    const tbody = table.querySelector('tbody');

    if (!input || !tbody) return;

    input.addEventListener('input', () => {
        const filter = input.value.toLowerCase().trim();
        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            if (!filter || text.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
