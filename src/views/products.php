<div id="products" class="view hidden">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Gestão de Produtos</h1>
        <button onclick="openProductModal()" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"><i class="fas fa-plus-circle mr-2"></i>Cadastrar Novo Produto</button>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-md overflow-x-auto">
        <table class="w-full text-left min-w-[700px]">
            <thead>
                <tr class="border-b">
                    <th class="p-3">Produto</th>
                    <th class="p-3">Tamanho</th>
                    <th class="p-3">Cor</th>
                    <th class="p-3">Preço</th>
                    <th class="p-3">Estoque</th>
                    <th class="p-3 text-center">Ações</th>
                </tr>
            </thead>
            <tbody id="productTableBody">
            </tbody>
        </table>
    </div>
</div>