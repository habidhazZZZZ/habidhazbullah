<?php
// Keamanan: Mencegah akses langsung ke file ini
if (!defined('IS_INCLUDED')) {
    die("Akses langsung tidak diizinkan.");
}
?>
<style>
    /* Style untuk baris header grup */
    .group-header td {
        background-color: #374151; /* bg-gray-700 */
        color: #d1d5db; /* text-gray-300 */
        font-weight: bold;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
        padding-left: 1rem;
    }
</style>

<div class="bg-gray-800 p-6 rounded-lg">
    <h3 class="text-xl font-semibold mb-4">Firewall - Filter Rules</h3>
    
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">#</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Chain</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Action</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Protocol</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Src. Address</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">Dst. Port</th>
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider">In. Interface</th>
                    <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody id="rules-table-body" class="divide-y divide-gray-600">
                <tr><td colspan="8" class="text-center py-4 text-gray-400">Memuat data filter rules...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const rulesBody = document.getElementById('rules-table-body');

    function fetchFilterRules() {
        fetch('../api/get_filter_rules.php')
            .then(res => res.ok ? res.json() : Promise.reject('Gagal memuat data'))
            .then(result => {
                if (result.status !== 'success' || !result.data) {
                    throw new Error(result.message || 'Data filter rules tidak ditemukan.');
                }
                
                let rules = result.data;
                rulesBody.innerHTML = ''; 
                
                if (rules.length === 0) {
                    rulesBody.innerHTML = `<tr><td colspan="8" class="text-center py-4">Tidak ada filter rule yang dikonfigurasi.</td></tr>`;
                    return;
                }

                // Mengurutkan rules berdasarkan komentar
                rules.sort((a, b) => (a.comment || '').localeCompare(b.comment || ''));

                let lastComment = null;

                rules.forEach((rule, index) => {
                    const currentComment = rule.comment || 'Tanpa Komentar';

                    // Jika komentar berubah, tambahkan baris header grup
                    if (currentComment !== lastComment) {
                        const groupHeaderRow = `<tr class="group-header"><td colspan="8">${currentComment}</td></tr>`;
                        rulesBody.innerHTML += groupHeaderRow;
                        lastComment = currentComment;
                    }

                    const disabled = rule.disabled === 'true';
                    const actionClass = rule.action === 'drop' ? 'text-red-400' : (rule.action === 'accept' ? 'text-green-400' : 'text-yellow-400');
                    
                    const row = `
                        <tr class="${disabled ? 'opacity-40' : ''} hover:bg-gray-700/50">
                            <td class="px-3 py-2 whitespace-nowrap">${rule['.id']}</td>
                            <td class="px-3 py-2 whitespace-nowrap font-semibold text-cyan-300">${rule.chain}</td>
                            <td class="px-3 py-2 whitespace-nowrap font-bold ${actionClass}">${rule.action}</td>
                            <td class="px-3 py-2 whitespace-nowrap">${rule.protocol || '-'}</td>
                            <td class="px-3 py-2 whitespace-nowrap font-mono text-sm">${rule['src-address'] || '-'}</td>
                            <td class="px-3 py-2 whitespace-nowrap font-mono text-sm">${rule['dst-port'] || '-'}</td>
                            <td class="px-3 py-2 whitespace-nowrap font-mono text-sm">${rule['in-interface'] || '-'}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${disabled ? 'bg-gray-600 text-gray-200' : 'bg-blue-800 text-blue-100'}">
                                    ${disabled ? 'Disabled' : 'Enabled'}
                                </span>
                            </td>
                        </tr>
                    `;
                    rulesBody.innerHTML += row;
                });
            })
            .catch(error => {
                console.error("Fetch error:", error);
                rulesBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-red-400">${error.message}</td></tr>`;
            });
    }

    fetchFilterRules();
});
</script>
