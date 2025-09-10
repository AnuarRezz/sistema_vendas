<div id="dashboard" class="view">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <section class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
            <div class="bg-blue-100 p-4 rounded-full">
                <i class="fas fa-dollar-sign text-blue-500 text-2xl" aria-label="Vendas Totais"></i>
            </div>
            <div>
                <p class="text-gray-500">Vendas Totais</p>
                <p id="totalSalesValue" class="text-2xl font-bold text-gray-800">R$ 0,00</p>
            </div>
        </section>
        <section class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
            <div class="bg-green-100 p-4 rounded-full">
                <i class="fas fa-shopping-cart text-green-500 text-2xl" aria-label="Vendas Realizadas"></i>
            </div>
            <div>
                <p class="text-gray-500">Vendas Realizadas</p>
                <p id="totalSalesCount" class="text-2xl font-bold text-gray-800">0</p>
            </div>
        </section>
        <section class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
            <div class="bg-yellow-100 p-4 rounded-full">
                <i class="fas fa-box-open text-yellow-500 text-2xl" aria-label="Itens em Estoque"></i>
            </div>
            <div>
                <p class="text-gray-500">Itens em Estoque</p>
                <p id="totalStock" class="text-2xl font-bold text-gray-800">0</p>
            </div>
        </section>
    </div>
</div>