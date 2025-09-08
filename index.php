<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vendas - Loja de Roupas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        .sidebar-icon { transition: all 0.2s ease-in-out; }
        .sidebar-icon:hover { transform: scale(1.1); }
        .payment-option input:checked + label {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #2563eb;
        }
        .modal-backdrop {
            background-color: rgba(0,0,0,0.5);
            transition: opacity 0.3s ease;
        }
        .product-list::-webkit-scrollbar { width: 8px; }
        .product-list::-webkit-scrollbar-track { background: #f1f1f1; }
        .product-list::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
        .product-list::-webkit-scrollbar-thumb:hover { background: #555; }
        .filter-btn.active { background-color: #3b82f6; color: white; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    <aside class="w-20 bg-gray-800 text-white flex flex-col items-center py-6 space-y-8">
        <div class="text-2xl font-bold">
            <i class="fas fa-store text-blue-400"></i>
        </div>
        <nav class="flex flex-col items-center space-y-6">
            <a href="#" onclick="showView('dashboard')" class="sidebar-icon p-3 rounded-lg" title="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
            </a>
            <a href="#" onclick="showView('pdv')" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Ponto de Venda (PDV)">
                <i class="fas fa-cash-register"></i>
            </a>
            <a href="#" onclick="showView('products')" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Produtos">
                <i class="fas fa-tshirt"></i>
            </a>
            <a href="#" onclick="showView('reports')" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Relatórios">
                <i class="fas fa-chart-line"></i>
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6 md:p-8 overflow-y-auto">
        <div id="dashboard" class="view">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-dollar-sign text-blue-500 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Vendas Totais</p>
                        <p id="totalSalesValue" class="text-2xl font-bold text-gray-800">R$ 0,00</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-shopping-cart text-green-500 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Vendas Realizadas</p>
                        <p id="totalSalesCount" class="text-2xl font-bold text-gray-800">0</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4">
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-box-open text-yellow-500 text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-gray-500">Itens em Estoque</p>
                        <p id="totalStock" class="text-2xl font-bold text-gray-800">0</p>
                    </div>
                </div>
            </div>
        </div>

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
    </main>

    <div id="alertModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl text-center max-w-sm w-full transform transition-transform scale-95">
            <div id="modalIcon" class="mb-4"></div>
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-2">Atenção</h3>
            <p id="modalMessage" class="text-gray-600 mb-6">Mensagem do modal.</p>
            <button onclick="closeModal('alertModal')" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors">OK</button>
        </div>
    </div>

    <div id="confirmationModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl text-center max-w-sm w-full transform transition-transform scale-95">
            <div id="confirmationModalIcon" class="mb-4"><i class="fas fa-question-circle text-5xl text-yellow-500"></i></div>
            <h3 id="confirmationModalTitle" class="text-xl font-bold text-gray-800 mb-2">Confirmar Ação</h3>
            <p id="confirmationModalMessage" class="text-gray-600 mb-6">Tem certeza que deseja continuar?</p>
            <div class="flex justify-center gap-4">
                <button onclick="closeModal('confirmationModal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
                <button id="confirmActionButton" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition-colors">Confirmar</button>
            </div>
        </div>
    </div>

    <div id="productModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4 overflow-auto">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg transform transition-transform scale-95 my-auto">
            <h3 id="productModalTitle" class="text-2xl font-bold text-gray-800 mb-6">Cadastrar Produto</h3>
            <form id="productForm" onsubmit="saveProduct(event)">
                <input type="hidden" id="productId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="productName" class="block mb-1 font-medium">Nome</label>
                        <input type="text" id="productName" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="productSize" class="block mb-1 font-medium">Tamanho</label>
                        <input type="text" id="productSize" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="productColor" class="block mb-1 font-medium">Cor</label>
                        <input type="text" id="productColor" class="w-full p-2 border rounded-lg" required>
                    </div>
                     <div>
                        <label for="productPrice" class="block mb-1 font-medium">Preço (R$)</label>
                        <input type="number" step="0.01" id="productPrice" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="productStock" class="block mb-1 font-medium">Estoque</label>
                        <input type="number" id="productStock" class="w-full p-2 border rounded-lg" required>
                    </div>
                    <div>
                        <label for="productImage" class="block mb-1 font-medium">Imagem do Produto</label>
                        <input type="file" id="productImage" accept="image/*" onchange="previewImage(event)" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block mb-1 font-medium">Preview:</label>
                    <img id="imagePreview" src="https://placehold.co/100x100/e0e0e0/777?text=Imagem" alt="Preview" class="w-24 h-24 object-cover rounded-lg border">
                </div>
                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" onclick="closeModal('productModal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors">Salvar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="saleModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg transform transition-transform scale-95">
            <h3 id="saleModalTitle" class="text-2xl font-bold text-gray-800 mb-6">Editar Venda</h3>
            <form id="saleForm" onsubmit="saveSale(event)">
                <input type="hidden" id="saleId">
                <div>
                    <label for="saleDate" class="block mb-1 font-medium">Data da Venda</label>
                    <input type="datetime-local" id="saleDate" class="w-full p-2 border rounded-lg" required>
                </div>
                <p class="text-sm text-gray-500 mt-4 bg-gray-100 p-3 rounded-lg"><i class="fas fa-info-circle mr-2"></i>A edição dos detalhes de pagamento não é permitida após a finalização da venda.</p>
                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" onclick="closeModal('saleModal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <div id="paymentSplitModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4 overflow-auto">
        <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-xl transform transition-transform scale-95 my-auto">
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Finalizar Pagamento</h3>
            <div class="flex justify-between items-end mb-6 border-b pb-4">
                <div>
                    <p class="text-gray-500">Total da Venda:</p>
                    <p id="splitModalTotal" class="text-4xl font-bold text-blue-600">R$ 0,00</p>
                </div>
                <div>
                    <p class="text-gray-500">Valor Restante:</p>
                    <p id="splitModalRemaining" class="text-2xl font-bold text-red-500">R$ 0,00</p>
                </div>
            </div>

            <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="paymentPartAmount" class="block mb-1 font-medium">Valor a Pagar</label>
                        <input type="number" id="paymentPartAmount" step="0.01" class="w-full p-2 border rounded-lg" placeholder="0,00">
                    </div>
                    <div>
                        <label for="paymentPartMethod" class="block mb-1 font-medium">Forma de Pagamento</label>
                        <select id="paymentPartMethod" class="w-full p-2 border rounded-lg bg-white appearance-none h-[42px]">
                            <option value="Dinheiro">Dinheiro</option>
                            <option value="Pix">Pix</option>
                            <option value="Cartão de Débito">Cartão de Débito</option>
                            <option value="Cartão de Crédito">Cartão de Crédito</option>
                            <option value="Link de Pagamento">Link de Pagamento</option>
                        </select>
                    </div>
                    <button onclick="addPaymentPart()" class="bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition-colors self-end h-[42px]">Adicionar Pagamento</button>
                </div>
            </div>

            <div class="mb-6">
                <h4 class="font-semibold mb-2">Pagamentos Registrados:</h4>
                <div id="paymentPartsList" class="space-y-2 max-h-32 overflow-y-auto bg-gray-50 p-3 rounded-lg">
                    <p class="text-gray-400 text-center italic">Nenhum pagamento adicionado.</p>
                </div>
            </div>
            
            <div class="flex justify-between gap-4 mt-8">
                <button type="button" onclick="cancelSplitPayment()" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar Venda</Gbutton>
                <button id="confirmSplitSaleButton" onclick="confirmSplitSale()" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>Confirmar Venda</button>
            </div>
        </div>
    </div>


<script>
    // --- SIMULAÇÃO DE BANCO DE DADOS ---
    let products = [
        { id: 101, name: 'Camiseta Básica', size: 'P', color: 'Branca', price: 49.90, stock: 15, image: 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta' },
        { id: 102, name: 'Camiseta Básica', size: 'M', color: 'Branca', price: 49.90, stock: 20, image: 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta' },
        { id: 103, name: 'Camiseta Básica', size: 'G', color: 'Branca', price: 49.90, stock: 10, image: 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta' },
        { id: 201, name: 'Camiseta Básica', size: 'P', color: 'Preta', price: 49.90, stock: 18, image: 'https://placehold.co/300x300/333333/FFFFFF?text=Camiseta' },
        { id: 202, name: 'Camiseta Básica', size: 'M', color: 'Preta', price: 49.90, stock: 25, image: 'https://placehold.co/300x300/333333/FFFFFF?text=Camiseta' },
        { id: 203, name: 'Camiseta Básica', size: 'G', color: 'Preta', price: 49.90, stock: 12, image: 'https://placehold.co/300x300/333333/FFFFFF?text=Camiseta' },
        { id: 301, name: 'Calça Jeans Skinny', size: '38', color: 'Azul Claro', price: 129.90, stock: 8, image: 'https://placehold.co/300x300/a0d2eb/333333?text=Calça' },
        { id: 302, name: 'Calça Jeans Skinny', size: '40', color: 'Azul Claro', price: 129.90, stock: 10, image: 'https://placehold.co/300x300/a0d2eb/333333?text=Calça' },
        { id: 303, name: 'Calça Jeans Skinny', size: '42', color: 'Azul Claro', price: 129.90, stock: 7, image: 'https://placehold.co/300x300/a0d2eb/333333?text=Calça' },
        { id: 401, name: 'Moletom com Capuz', size: 'M', color: 'Cinza', price: 159.90, stock: 5, image: 'https://placehold.co/300x300/cccccc/333333?text=Moletom' },
        { id: 402, name: 'Moletom com Capuz', size: 'G', color: 'Cinza', price: 159.90, stock: 8, image: 'https://placehold.co/300x300/cccccc/333333?text=Moletom' },
    ];
    
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    const lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);

    // Estrutura de Vendas Modificada para aceitar múltiplos pagamentos
    let sales = [
        { 
            id: 1, 
            date: yesterday, 
            items: [{id: 101, name: 'Camiseta Básica', size: 'P', color: 'Branca', price: 49.90, quantity: 1}], 
            total: 49.90, 
            payments: [{ method: 'Pix', amount: 49.90 }] 
        },
        { 
            id: 2, 
            date: lastMonth, 
            items: [{id: 301, name: 'Calça Jeans Skinny', size: '38', color: 'Azul Claro', price: 129.90, quantity: 1}], 
            total: 129.90, 
            payments: [{ method: 'Cartão de Crédito', amount: 129.90 }] 
        },
    ];
    let cart = [];
    let saleIdCounter = 3;
    let productIdCounter = 500; 
    let currentFilteredSales = [];
    
    // Variáveis para controle do pagamento dividido
    let currentSalePayments = [];
    let currentSaleTotal = 0;
    let currentSaleRemaining = 0;

    // --- CONTROLE DE NAVEGAÇÃO ---
    const views = document.querySelectorAll('.view');
    const sidebarIcons = document.querySelectorAll('.sidebar-icon');
    let currentFilter = 'all';
    
    function showView(viewId) {
        views.forEach(view => view.classList.add('hidden'));
        document.getElementById(viewId).classList.remove('hidden');

        sidebarIcons.forEach(icon => icon.classList.remove('bg-gray-700'));
        document.querySelector(`a[onclick="showView('${viewId}')"]`).classList.add('bg-gray-700');
        
        if (viewId === 'dashboard') updateDashboard();
        if (viewId === 'products') renderProductTable();
        if (viewId === 'reports') renderSalesReport('all');
        if (viewId === 'pdv') renderProductList();
    }

    // --- LÓGICA DO PDV (CARRINHO) ---
    function renderProductList() {
        const productList = document.getElementById('productList');
        const searchTerm = document.getElementById('productSearch').value.toLowerCase();
        productList.innerHTML = '';

        const filteredProducts = products.filter(p => 
            p.stock > 0 && 
            (p.name.toLowerCase().includes(searchTerm) || p.id.toString().includes(searchTerm))
        );

        if (filteredProducts.length === 0) {
            productList.innerHTML = `<p class="col-span-full text-center text-gray-500">Nenhum produto encontrado.</p>`;
            return;
        }

        filteredProducts.forEach(product => {
            productList.innerHTML += `
                <div onclick="addToCart(${product.id})" class="border rounded-lg p-3 text-center cursor-pointer hover:shadow-lg hover:border-blue-500 transition-all">
                    <img src="${product.image}" alt="${product.name}" class="w-full h-24 object-cover rounded-md mb-2">
                    <p class="font-semibold text-sm text-gray-700">${product.name}</p>
                    <p class="text-xs text-gray-500">${product.size} / ${product.color}</p>
                    <p class="font-bold text-blue-600 mt-1">R$ ${product.price.toFixed(2).replace('.', ',')}</p>
                </div>
            `;
        });
    }

    function addToCart(productId) {
        const product = products.find(p => p.id === productId);
        if (!product || product.stock <= 0) {
            showAlert('Produto esgotado!', 'error');
            return;
        }

        const cartItem = cart.find(item => item.id === productId);

        if (cartItem) {
            if (cartItem.quantity < product.stock) {
                cartItem.quantity++;
            } else {
                showAlert('Quantidade máxima em estoque atingida!', 'warning');
            }
        } else {
            cart.push({ ...product, quantity: 1 });
        }
        updateCart();
    }
    
    function updateCart() {
        const cartItemsDiv = document.getElementById('cartItems');
        const cartTotalSpan = document.getElementById('cartTotal');
        let total = 0;

        if (cart.length === 0) {
            cartItemsDiv.innerHTML = '<p class="text-gray-500">Nenhum item no carrinho.</p>';
            cartTotalSpan.innerText = 'R$ 0,00';
            return;
        }

        cartItemsDiv.innerHTML = '';
        cart.forEach((item, index) => {
            total += item.price * item.quantity;
            cartItemsDiv.innerHTML += `
                <div class="flex justify-between items-center mb-2 text-sm">
                    <div>
                        <p class="font-semibold text-gray-800">${item.name} (${item.size}/${item.color})</p>
                        <p class="text-gray-600">R$ ${item.price.toFixed(2).replace('.', ',')} x ${item.quantity}</p>
                    </div>
                    <div>
                        <button onclick="changeQuantity(${index}, -1)" class="px-2 text-red-500"><i class="fas fa-minus-circle"></i></button>
                        <button onclick="removeFromCart(${index})" class="px-2 text-red-700"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
        });

        cartTotalSpan.innerText = `R$ ${total.toFixed(2).replace('.', ',')}`;
    }
    
    function changeQuantity(cartIndex, amount) {
        const item = cart[cartIndex];
        const product = products.find(p => p.id === item.id);
        
        if (item.quantity + amount > 0 && item.quantity + amount <= product.stock) {
            item.quantity += amount;
        } else if (item.quantity + amount <= 0) {
            removeFromCart(cartIndex);
        } else {
            showAlert('Quantidade máxima em estoque atingida!', 'warning');
        }
        updateCart();
    }
    
    function removeFromCart(cartIndex) {
        cart.splice(cartIndex, 1);
        updateCart();
    }

    // --- LÓGICA DE PAGAMENTO DIVIDIDO ---

    function openSplitPaymentModal() {
        if (cart.length === 0) {
            showAlert('O carrinho está vazio!', 'warning');
            return;
        }
        
        currentSaleTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        currentSalePayments = [];
        updateSplitPaymentModalUI();

        const modal = document.getElementById('paymentSplitModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.querySelector('.transform').classList.add('scale-100'), 10);
    }
    
    function updateSplitPaymentModalUI() {
        const totalPaid = currentSalePayments.reduce((sum, p) => sum + p.amount, 0);
        currentSaleRemaining = currentSaleTotal - totalPaid;

        document.getElementById('splitModalTotal').innerText = `R$ ${currentSaleTotal.toFixed(2).replace('.', ',')}`;
        document.getElementById('splitModalRemaining').innerText = `R$ ${currentSaleRemaining.toFixed(2).replace('.', ',')}`;
        document.getElementById('paymentPartAmount').value = currentSaleRemaining > 0.005 ? currentSaleRemaining.toFixed(2) : ''; // Preenche com o restante

        // Renderiza lista de pagamentos parciais
        const paymentPartsList = document.getElementById('paymentPartsList');
        paymentPartsList.innerHTML = '';
        if (currentSalePayments.length === 0) {
            paymentPartsList.innerHTML = '<p class="text-gray-400 text-center italic">Nenhum pagamento adicionado.</p>';
        } else {
            currentSalePayments.forEach((p, index) => {
                paymentPartsList.innerHTML += `
                    <div class="flex justify-between items-center bg-white p-2 rounded shadow-sm text-sm">
                        <span>${index + 1}. ${p.method}</span>
                        <span class="font-medium">R$ ${p.amount.toFixed(2).replace('.', ',')}</span>
                    </div>
                `;
            });
        }

        // Habilita/desabilita botão de confirmação
        const confirmButton = document.getElementById('confirmSplitSaleButton');
        // Usar uma pequena tolerância para comparações de ponto flutuante
        if (Math.abs(currentSaleRemaining) < 0.001) {
            confirmButton.disabled = false;
        } else {
            confirmButton.disabled = true;
        }
    }

    function addPaymentPart() {
        const amountInput = document.getElementById('paymentPartAmount');
        const methodInput = document.getElementById('paymentPartMethod');
        const amount = parseFloat(amountInput.value);

        if (isNaN(amount) || amount <= 0) {
            showAlert('Valor de pagamento inválido.', 'warning');
            return;
        }
        
        // Evitar pagamento excessivo (com tolerância)
        if (amount > currentSaleRemaining + 0.001) {
            showAlert(`O valor não pode ser maior que o restante (R$ ${currentSaleRemaining.toFixed(2)}).`, 'warning');
            return;
        }

        currentSalePayments.push({
            method: methodInput.value,
            amount: amount
        });
        
        updateSplitPaymentModalUI();
    }
    
    function confirmSplitSale() {
        const newSale = {
            id: saleIdCounter++,
            date: new Date(),
            items: JSON.parse(JSON.stringify(cart)), 
            total: currentSaleTotal,
            payments: currentSalePayments
        };

        sales.push(newSale);

        // Deduzir estoque
        cart.forEach(cartItem => {
            const product = products.find(p => p.id === cartItem.id);
            if (product) {
                product.stock -= cartItem.quantity;
            }
        });

        // Limpar carrinho e resetar estado
        cart = [];
        updateCart();
        renderProductList();
        closeModal('paymentSplitModal');
        showAlert('Venda finalizada com sucesso!', 'success');
    }
    
    function cancelSplitPayment() {
        closeModal('paymentSplitModal');
        // Não limpa o carrinho, apenas cancela a operação de pagamento
    }

    // --- LÓGICA DE PRODUTOS (CRUD com Upload) ---
    function previewImage(event) {
        const reader = new FileReader();
        const imagePreview = document.getElementById('imagePreview');
        reader.onload = function(){
            imagePreview.src = reader.result;
        }
        if(event.target.files[0]){
            reader.readAsDataURL(event.target.files[0]);
            imagePreview.classList.remove('hidden');
        } else {
            imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
        }
    }

    function openProductModal(productId = null) {
        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');
        const title = document.getElementById('productModalTitle');
        const imagePreview = document.getElementById('imagePreview');
        form.reset();
        document.getElementById('productId').value = '';

        if(productId) {
            const product = products.find(p => p.id === productId);
            title.innerText = 'Editar Produto';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productSize').value = product.size;
            document.getElementById('productColor').value = product.color;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            imagePreview.src = product.image; // Mostra imagem atual
        } else {
            title.innerText = 'Cadastrar Produto';
            imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem'; // Imagem padrão
        }

        modal.classList.remove('hidden');
        setTimeout(() => modal.querySelector('.transform').classList.add('scale-100'), 10);
    }

    function saveProduct(event) {
        event.preventDefault();
        const id = document.getElementById('productId').value;
        const fileInput = document.getElementById('productImage');
        const file = fileInput.files[0];

        const productData = {
            id: id ? parseInt(id) : productIdCounter++,
            name: document.getElementById('productName').value,
            size: document.getElementById('productSize').value,
            color: document.getElementById('productColor').value,
            price: parseFloat(document.getElementById('productPrice').value),
            stock: parseInt(document.getElementById('productStock').value),
            image: '' // Será definida abaixo
        };

        // Lógica de manipulação da imagem
        if (file) {
            productData.image = URL.createObjectURL(file);
        } else if (id) {
            productData.image = products.find(p => p.id == id).image; // Manter imagem existente se nenhuma nova for enviada
        } else {
            productData.image = `https://placehold.co/300x300/cccccc/333333?text=${productData.name.replace(/\s/g,'+')}`; // Placeholder padrão
        }

        if(id) { 
            const productIndex = products.findIndex(p => p.id == id);
            if(productIndex > -1) {
                products[productIndex] = productData;
                showAlert('Produto atualizado com sucesso!', 'success');
            }
        } else { 
            products.push(productData);
            showAlert('Produto cadastrado com sucesso!', 'success');
        }

        closeModal('productModal');
        renderProductTable();
        renderProductList();
    }

    function deleteProduct(productId) {
        showConfirmationModal('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.', () => {
             const productIndex = products.findIndex(p => p.id === productId);
             if (productIndex > -1) {
                 products.splice(productIndex, 1);
                 showAlert('Produto excluído com sucesso!', 'success');
                 renderProductTable();
                 renderProductList();
             }
        });
    }

    // --- LÓGICA DAS OUTRAS TELAS ---
    function updateDashboard() {
        const totalSalesValue = sales.reduce((sum, sale) => sum + sale.total, 0);
        const totalSalesCount = sales.length;
        const totalStock = products.reduce((sum, p) => sum + p.stock, 0);

        document.getElementById('totalSalesValue').innerText = `R$ ${totalSalesValue.toFixed(2).replace('.', ',')}`;
        document.getElementById('totalSalesCount').innerText = totalSalesCount;
        document.getElementById('totalStock').innerText = totalStock;
    }

    function renderProductTable() {
        const tableBody = document.getElementById('productTableBody');
        tableBody.innerHTML = '';
        products.sort((a, b) => a.name.localeCompare(b.name) || a.size.localeCompare(b.size)).forEach(p => {
            tableBody.innerHTML += `
                <tr class="border-b hover:bg-gray-50 align-middle">
                    <td class="p-3 flex items-center gap-3">
                        <img src="${p.image}" alt="${p.name}" class="w-12 h-12 object-cover rounded-md border">
                        <span class="font-medium">${p.name}</span>
                    </td>
                    <td class="p-3">${p.size}</td>
                    <td class="p-3">${p.color}</td>
                    <td class="p-3">R$ ${p.price.toFixed(2).replace('.', ',')}</td>
                    <td class="p-3 ${p.stock < 5 ? 'text-red-500 font-bold' : ''}">${p.stock}</td>
                    <td class="p-3 text-center">
                        <button onclick="openProductModal(${p.id})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteProduct(${p.id})" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });
    }

    // --- LÓGICA DE RELATÓRIOS (VENDAS) ---
    function openSaleModal(saleId) {
        const modal = document.getElementById('saleModal');
        const sale = sales.find(s => s.id === saleId);
        
        if (sale) {
            document.getElementById('saleId').value = sale.id;
            const saleDate = new Date(sale.date);
            const formattedDate = `${saleDate.getFullYear()}-${String(saleDate.getMonth() + 1).padStart(2, '0')}-${String(saleDate.getDate()).padStart(2, '0')}T${String(saleDate.getHours()).padStart(2, '0')}:${String(saleDate.getMinutes()).padStart(2, '0')}`;
            document.getElementById('saleDate').value = formattedDate;
            
            modal.classList.remove('hidden');
            setTimeout(() => modal.querySelector('.transform').classList.add('scale-100'), 10);
        }
    }

    function saveSale(event) {
        event.preventDefault();
        const id = document.getElementById('saleId').value;
        const saleIndex = sales.findIndex(s => s.id == id);

        if (saleIndex > -1) {
            sales[saleIndex].date = new Date(document.getElementById('saleDate').value);
            showAlert('Data da venda atualizada com sucesso!', 'success');
        }
        
        closeModal('saleModal');
        renderSalesReport(currentFilter);
    }

    function deleteSale(saleId) {
        showConfirmationModal('Tem certeza que deseja excluir esta venda? O estoque dos itens será devolvido.', () => {
            const saleIndex = sales.findIndex(s => s.id === saleId);
            if (saleIndex > -1) {
                const saleToDelete = sales[saleIndex];

                saleToDelete.items.forEach(item => {
                    const product = products.find(p => p.id === item.id);
                    if (product) {
                        product.stock += item.quantity;
                    }
                });

                sales.splice(saleIndex, 1);
                showAlert('Venda excluída com sucesso!', 'success');
                renderSalesReport(currentFilter);
                updateDashboard();
                renderProductList(); // Atualiza lista do PDV se estoque mudou
            }
        });
    }

    function clearFilters() {
        document.getElementById('daily-report-date').value = '';
        document.getElementById('monthly-report-date').value = '';
        document.getElementById('payment-filter').value = '';
        renderSalesReport('all');
    }

    // FUNÇÃO RENDERIZAR RELATÓRIO (MODIFICADA para pagamento dividido)
    function renderSalesReport(filterType) {
        if (filterType !== 'custom_day' && filterType !== 'custom_month') {
             currentFilter = filterType;
        }
       
        const reportBody = document.getElementById('salesReportBody');
        const paymentFilter = document.getElementById('payment-filter').value;
        let filteredSalesSource = sales;
        const now = new Date();

        // 1. Filtro por Data (Lógica existente)
        switch(filterType) {
            case 'today':
                filteredSalesSource = sales.filter(sale => new Date(sale.date).toDateString() === now.toDateString());
                break;
            case 'this_month':
                filteredSalesSource = sales.filter(sale => 
                    new Date(sale.date).getMonth() === now.getMonth() &&
                    new Date(sale.date).getFullYear() === now.getFullYear()
                );
                break;
            case 'custom_day':
                const selectedDate = document.getElementById('daily-report-date').value;
                if (selectedDate) {
                    const targetDate = new Date(selectedDate + 'T00:00:00');
                    filteredSalesSource = sales.filter(sale => new Date(sale.date).toDateString() === targetDate.toDateString());
                    currentFilter = 'custom_day';
                } else if (currentFilter === 'custom_day') {
                    currentFilter = 'all'; // Reset se data for limpa
                }
                break;
             case 'custom_month':
                const selectedMonth = document.getElementById('monthly-report-date').value; 
                if (selectedMonth) {
                    const [year, month] = selectedMonth.split('-').map(Number);
                    filteredSalesSource = sales.filter(sale => {
                        const saleDate = new Date(sale.date);
                        return saleDate.getFullYear() === year && saleDate.getMonth() + 1 === month;
                    });
                    currentFilter = 'custom_month';
                } else if (currentFilter === 'custom_month') {
                    currentFilter = 'all'; // Reset se data for limpa
                }
                break;
            case 'all':
            default:
                 // Se o filtro não for de data específico, resetamos para a fonte completa antes de aplicar outros filtros.
                 if (document.getElementById('daily-report-date').value) renderSalesReport('custom_day');
                 else if (document.getElementById('monthly-report-date').value) renderSalesReport('custom_month');
                 else filteredSalesSource = sales;
                break;
        }
        
        // 2. Filtro por Forma de Pagamento (Modificado para array 'payments')
        if (paymentFilter) {
            filteredSalesSource = filteredSalesSource.filter(sale => {
                if (paymentFilter === 'Cartão') {
                    return sale.payments.some(p => p.method === 'Cartão de Crédito' || p.method === 'Cartão de Débito');
                } else {
                    return sale.payments.some(p => p.method === paymentFilter);
                }
            });
        }
        
        currentFilteredSales = filteredSalesSource;

        // Atualiza botões de filtro rápido
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        const activeButton = document.querySelector(`.filter-btn[onclick="renderSalesReport('${currentFilter}')"]`);
        if (activeButton) activeButton.classList.add('active');

        // 3. Renderização da Tabela de Relatório
        reportBody.innerHTML = '';
        if (currentFilteredSales.length === 0) {
             reportBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhuma venda encontrada para os filtros selecionados.</td></tr>`;
        } else {
            currentFilteredSales.slice().reverse().forEach(sale => {
                const itemsSummary = sale.items.map(i => `${i.quantity}x ${i.name.substring(0, 15)}...`).join('<br>');
                // Formata o sumário de pagamentos
                const paymentSummary = sale.payments.map(p => {
                    return `${p.method} (R$ ${p.amount.toFixed(2).replace('.', ',')})`;
                }).join('<br>');

                reportBody.innerHTML += `
                    <tr class="border-b hover:bg-gray-50 align-top">
                        <td class="p-3 font-medium">#${sale.id}</td>
                        <td class="p-3">${new Date(sale.date).toLocaleString('pt-BR')}</td>
                        <td class="p-3 text-xs">${itemsSummary}</td>
                        <td class="p-3 font-semibold">R$ ${sale.total.toFixed(2).replace('.', ',')}</td>
                        <td class="p-3 text-xs font-medium">${paymentSummary}</td>
                        <td class="p-3 text-center">
                            <button onclick="openSaleModal(${sale.id})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar Data"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteSale(${sale.id})" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
        }
        
        // 4. Cálculo e Renderização dos Resumos (Modificado para array 'payments')
        let totalValue = 0;
        let totalDinheiro = 0;
        let totalPix = 0;
        let totalCartao = 0;
        let totalLink = 0;

        currentFilteredSales.forEach(sale => {
            totalValue += sale.total;
            sale.payments.forEach(payment => {
                if (payment.method === 'Dinheiro') totalDinheiro += payment.amount;
                else if (payment.method === 'Pix') totalPix += payment.amount;
                else if (payment.method === 'Cartão de Crédito' || payment.method === 'Cartão de Débito') totalCartao += payment.amount;
                else if (payment.method === 'Link de Pagamento') totalLink += payment.amount;
            });
        });

        document.getElementById('summaryTotalValue').innerText = `R$ ${totalValue.toFixed(2).replace('.', ',')}`;
        document.getElementById('summaryTotalCount').innerText = currentFilteredSales.length;
        document.getElementById('summaryDinheiro').innerText = `R$ ${totalDinheiro.toFixed(2).replace('.', ',')}`;
        document.getElementById('summaryPix').innerText = `R$ ${totalPix.toFixed(2).replace('.', ',')}`;
        document.getElementById('summaryCartao').innerText = `R$ ${totalCartao.toFixed(2).replace('.', ',')}`;
        document.getElementById('summaryLink').innerText = `R$ ${totalLink.toFixed(2).replace('.', ',')}`;
    }

    // --- FUNÇÕES DE EXPORTAÇÃO (Modificadas para array 'payments') ---
    function exportReport(format) {
        if (currentFilteredSales.length === 0) {
            showAlert('Não há dados filtrados para exportar.', 'warning');
            return;
        }
        if (format === 'pdf') exportToPDF();
        else if (format === 'excel') exportToExcel();
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const tableRows = [];
        currentFilteredSales.forEach(sale => {
            const saleDate = new Date(sale.date).toLocaleString('pt-BR');
            const itemsSummary = sale.items.map(i => `${i.quantity}x ${i.name} (${i.size}/${i.color})`).join(', ');
            const total = `R$ ${sale.total.toFixed(2).replace('.', ',')}`;
            const paymentSummary = sale.payments.map(p => `${p.method}: R$ ${p.amount.toFixed(2)}`).join('; ');
            tableRows.push([sale.id, saleDate, itemsSummary, total, paymentSummary]);
        });
        doc.autoTable({
            head: [["ID", "Data", "Itens", "Total", "Pagamento"]],
            body: tableRows, startY: 20, styles: { fontSize: 8 }, headStyles: { fillColor: [22, 160, 133] },
            didDrawPage: data => doc.text("Relatório de Vendas Filtrado", data.settings.margin.left, 15)
        });
        doc.save("relatorio_vendas.pdf");
    }

    function exportToExcel() {
        const dataToExport = currentFilteredSales.map(sale => {
            const itemsSummary = sale.items.map(i => `${i.quantity}x ${i.name} (${i.size}/${i.color})`).join(', ');
            const paymentSummary = sale.payments.map(p => `${p.method}: R$ ${p.amount.toFixed(2)}`).join('; ');
            return {
                "ID Venda": sale.id, "Data": new Date(sale.date).toLocaleString('pt-BR'),
                "Itens": itemsSummary, "Total (R$)": sale.total, "Forma de Pagamento": paymentSummary
            };
        });
        const ws = XLSX.utils.json_to_sheet(dataToExport);
        ws['!cols'] = [ { wch: 10 }, { wch: 20 }, { wch: 50 }, { wch: 15 }, { wch: 30 } ];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Vendas");
        XLSX.writeFile(wb, "relatorio_vendas.xlsx");
    }

    // --- CONTROLE DE MODAIS ---
    function showAlert(message, type = 'info') {
        const modal = document.getElementById('alertModal');
        document.getElementById('modalMessage').innerText = message;
        const iconDiv = document.getElementById('modalIcon');
        const titleDiv = document.getElementById('modalTitle');
        switch(type) {
            case 'success':
                titleDiv.innerText = 'Sucesso!';
                iconDiv.innerHTML = `<i class="fas fa-check-circle text-5xl text-green-500"></i>`; break;
            case 'error':
                titleDiv.innerText = 'Erro!';
                iconDiv.innerHTML = `<i class="fas fa-times-circle text-5xl text-red-500"></i>`; break;
            case 'warning':
                titleDiv.innerText = 'Atenção!';
                iconDiv.innerHTML = `<i class="fas fa-exclamation-triangle text-5xl text-yellow-500"></i>`; break;
        }
        modal.classList.remove('hidden');
        setTimeout(() => modal.querySelector('.transform').classList.add('scale-100'), 10);
    }
    
    function showConfirmationModal(message, callback) {
        const modal = document.getElementById('confirmationModal');
        document.getElementById('confirmationModalMessage').innerText = message;
        const confirmButton = document.getElementById('confirmActionButton');
        const newConfirmButton = confirmButton.cloneNode(true);
        confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
        newConfirmButton.onclick = () => { callback(); closeModal('confirmationModal'); };
        modal.classList.remove('hidden');
        setTimeout(() => modal.querySelector('.transform').classList.add('scale-100'), 10);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.querySelector('.transform').classList.remove('scale-100');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    // --- INICIALIZAÇÃO ---
    document.addEventListener('DOMContentLoaded', () => {
        showView('dashboard');
    });
</script>
</body>
</html>