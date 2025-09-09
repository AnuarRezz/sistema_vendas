<div id="pdv" class="view hidden">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Ponto de Venda (PDV)</h1>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-xl shadow-md">
                <input type="text" id="productSearch" onkeyup="renderProductList()" placeholder="Buscar produto por nome ou código..." class="w-full p-3 border rounded-lg mb-4 focus:ring-2 focus:ring-blue-500">
                <div id="productList" class="product-list grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 h-[60vh] overflow-y-auto pr-2">
                </div>
            </div>
        </div>
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-xl shadow-md sticky top-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Carrinho</h2>
                <div id="cartItems" class="mb-4 h-64 overflow-y-auto">
                   <p class="text-gray-500">Nenhum item no carrinho.</p>
                </div>
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-lg font-medium text-gray-600">Total:</span>
                        <span id="cartTotal" class="text-2xl font-bold text-gray-800">R$ 0,00</span>
                    </div>
                    <button onclick="openSplitPaymentModal()" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg mt-5 hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>Realizar Pagamento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>