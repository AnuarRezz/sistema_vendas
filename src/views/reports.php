<div id="reports" class="view hidden">
    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h1 class="text-3xl font-bold text-gray-800">Relatório de Vendas</h1>
        <div class="flex gap-3">
             <button onclick="exportReport('pdf')" class="bg-red-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-600 transition-colors text-sm">
                 <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
             </button>
             <button onclick="exportReport('excel')" class="bg-green-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors text-sm">
                 <i class="fas fa-file-excel mr-2"></i>Exportar Excel
             </button>
        </div>
    </div>
    <div class="bg-white p-4 rounded-xl shadow-md mb-6 flex flex-wrap items-center gap-4 text-sm md:text-base">
        <div>
            <span class="font-semibold mr-3">Filtros rápidos:</span>
            <button onclick="renderSalesReport('today')" class="filter-btn bg-gray-200 px-3 py-1 rounded-lg hover:bg-gray-300 transition">Hoje</button>
            <button onclick="renderSalesReport('this_month')" class="filter-btn bg-gray-200 px-3 py-1 rounded-lg hover:bg-gray-300 transition">Este Mês</button>
            <button onclick="renderSalesReport('all')" class="filter-btn bg-gray-200 px-3 py-1 rounded-lg hover:bg-gray-300 transition active">Todas</button>
        </div>
        <div class="flex items-center gap-2">
            <label for="daily-report-date">Ver por dia:</label>
            <input type="date" id="daily-report-date" onchange="renderSalesReport('custom_day')" class="border p-1 rounded-lg">
        </div>
         <div class="flex items-center gap-2">
            <label for="monthly-report-date">Ver por mês:</label>
            <input type="month" id="monthly-report-date" onchange="renderSalesReport('custom_month')" class="border p-1 rounded-lg">
        </div>
        <div class="flex items-center gap-2">
            <label for="payment-filter">Pagamento:</label>
            <select id="payment-filter" onchange="renderSalesReport(currentFilter)" class="border p-1 rounded-lg bg-white">
                <option value="">Todos</option>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Pix">Pix</option>
                <option value="Cartão">Cartão (Crédito/Débito)</option>
                <option value="Link de Pagamento">Link de Pagamento</option>
            </select>
        </div>
        <button onclick="clearFilters()" class="text-blue-600 hover:underline text-sm ml-auto">Limpar Filtros</button>
    </div>
    <div id="report-summary" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
         <div class="bg-white p-6 rounded-xl shadow-md">
            <p class="text-gray-500">Valor Total no Período</p>
            <p id="summaryTotalValue" class="text-2xl font-bold text-gray-800">R$ 0,00</p>
         </div>
         <div class="bg-white p-6 rounded-xl shadow-md">
            <p class="text-gray-500">Nº de Vendas no Período</p>
            <p id="summaryTotalCount" class="text-2xl font-bold text-gray-800">0</p>
         </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md mb-6">
        <h4 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Totais por Forma de Pagamento (no período)</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <p class="text-gray-500"><i class="fas fa-money-bill-wave text-green-500 mr-1"></i> Dinheiro</p>
                <p id="summaryDinheiro" class="text-xl font-bold text-gray-800 mt-1">R$ 0,00</p>
            </div>
            <div>
                <p class="text-gray-500"><i class="fa-brands fa-pix text-blue-500 mr-1"></i> Pix</p>
                <p id="summaryPix" class="text-xl font-bold text-gray-800 mt-1">R$ 0,00</p>
            </div>
            <div>
                <p class="text-gray-500"><i class="fas fa-credit-card text-indigo-500 mr-1"></i> Cartão</p>
                <p id="summaryCartao" class="text-xl font-bold text-gray-800 mt-1">R$ 0,00</p>
            </div>
            <div>
                <p class="text-gray-500"><i class="fas fa-link text-purple-500 mr-1"></i> Link Pagto.</p>
                <p id="summaryLink" class="text-xl font-bold text-gray-800 mt-1">R$ 0,00</p>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
        <table class="w-full text-left min-w-[800px]">
            <thead>
                <tr class="border-b">
                    <th class="p-3">ID Venda</th>
                    <th class="p-3">Data</th>
                    <th class="p-3">Itens</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Pagamento</th>
                    <th class="p-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody id="salesReportBody">
            </tbody>
        </table>
    </div>
</div>