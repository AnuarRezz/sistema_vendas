// --- VARIÁVEIS GLOBAIS ---
let cart = [];
let currentFilteredSales = [];
let currentSalePayments = [];
let currentSaleTotal = 0;
let currentSaleRemaining = 0;
const views = document.querySelectorAll('.view');
const sidebarIcons = document.querySelectorAll('.sidebar-icon');
let currentFilter = 'all';

// --- FUNÇÕES DE API ---

// Busca dados da API
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Falha ao buscar dados:", error);
        showAlert(`Erro ao carregar dados da API: ${error.message}`, 'error');
        return null;
    }
}

// Envia dados para a API
async function postData(url, data, method = 'POST') {
    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Falha ao enviar dados:", error);
        showAlert(`Erro ao salvar dados: ${error.message}`, 'error');
        return null;
    }
}


// --- NAVEGAÇÃO E EXIBIÇÃO ---

function showView(viewId) {
    views.forEach(view => view.classList.add('hidden'));
    const viewElement = document.getElementById(viewId);
    if (viewElement) {
        viewElement.classList.remove('hidden');
    }

    sidebarIcons.forEach(icon => icon.classList.remove('bg-gray-700'));
    const activeIcon = document.querySelector(`a[href="?page=${viewId}"]`);
    if (activeIcon) {
        activeIcon.classList.add('bg-gray-700');
    }

    // Carrega os dados necessários para a view
    if (viewId === 'dashboard') updateDashboard();
    if (viewId === 'products') renderProductTable();
    if (viewId === 'reports') renderSalesReport('all');
    if (viewId === 'pdv') renderProductList();
}

// --- PDV (Ponto de Venda) ---

async function renderProductList() {
    const productList = document.getElementById('productList');
    if (!productList) return;

    const products = await fetchData('api/products.php');
    if (!products) return;

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
            <div onclick='addToCart(${JSON.stringify(product)})' class="border rounded-lg p-3 text-center cursor-pointer hover:shadow-lg hover:border-blue-500 transition-all">
                <img src="${product.image}" alt="${product.name}" class="w-full h-24 object-cover rounded-md mb-2">
                <p class="font-semibold text-sm text-gray-700">${product.name}</p>
                <p class="text-xs text-gray-500">${product.size} / ${product.color}</p>
                <p class="font-bold text-blue-600 mt-1">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</p>
            </div>
        `;
    });
}


function addToCart(product) {
    if (!product || product.stock <= 0) {
        showAlert('Produto esgotado!', 'error');
        return;
    }

    const cartItem = cart.find(item => item.id === product.id);

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
    if (!cartItemsDiv || !cartTotalSpan) return;
    
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
                    <p class="text-gray-600">R$ ${parseFloat(item.price).toFixed(2).replace('.', ',')} x ${item.quantity}</p>
                </div>
                <div>
                    <button onclick="changeQuantity(${index}, 1)" class="px-2 text-green-500"><i class="fas fa-plus-circle"></i></button>
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

    if (item.quantity + amount > 0 && item.quantity + amount <= item.stock) {
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


// --- MODAIS DE PAGAMENTO E VENDA ---

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
}

function updateSplitPaymentModalUI() {
    const totalPaid = currentSalePayments.reduce((sum, p) => sum + p.amount, 0);
    currentSaleRemaining = currentSaleTotal - totalPaid;

    document.getElementById('splitModalTotal').innerText = `R$ ${currentSaleTotal.toFixed(2).replace('.', ',')}`;
    document.getElementById('splitModalRemaining').innerText = `R$ ${currentSaleRemaining.toFixed(2).replace('.', ',')}`;
    document.getElementById('paymentPartAmount').value = currentSaleRemaining > 0.005 ? currentSaleRemaining.toFixed(2) : '';

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
                     <button onclick="removePaymentPart(${index})" class="text-red-500"><i class="fas fa-times"></i></button>
                </div>
            `;
        });
    }

    const confirmButton = document.getElementById('confirmSplitSaleButton');
    confirmButton.disabled = Math.abs(currentSaleRemaining) >= 0.01;
}

function addPaymentPart() {
    const amountInput = document.getElementById('paymentPartAmount');
    const methodInput = document.getElementById('paymentPartMethod');
    const amount = parseFloat(amountInput.value);

    if (isNaN(amount) || amount <= 0) {
        showAlert('Valor de pagamento inválido.', 'warning');
        return;
    }

    if (amount > currentSaleRemaining + 0.01) {
        showAlert(`O valor não pode ser maior que o restante (R$ ${currentSaleRemaining.toFixed(2).replace('.',',')}).`, 'warning');
        return;
    }

    currentSalePayments.push({
        method: methodInput.value,
        amount: amount
    });

    updateSplitPaymentModalUI();
}

function removePaymentPart(index) {
    currentSalePayments.splice(index, 1);
    updateSplitPaymentModalUI();
}

async function confirmSplitSale() {
    const saleData = {
        items: cart,
        total: currentSaleTotal,
        payments: currentSalePayments
    };

    const result = await postData('api/sales.php', saleData);

    if (result && result.status === 'success') {
        cart = [];
        updateCart();
        renderProductList(); // Atualiza a lista de produtos com novo estoque
        closeModal('paymentSplitModal');
        showAlert('Venda finalizada com sucesso!', 'success');
    }
}

function cancelSplitPayment() {
    closeModal('paymentSplitModal');
}


// --- GESTÃO DE PRODUTOS ---

function previewImage(event) {
    const reader = new FileReader();
    const imagePreview = document.getElementById('imagePreview');
    reader.onload = function(){
        imagePreview.src = reader.result;
    }
    if(event.target.files[0]){
        reader.readAsDataURL(event.target.files[0]);
    } else {
        imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
    }
}

async function openProductModal(productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('productModalTitle');
    const imagePreview = document.getElementById('imagePreview');
    form.reset();
    document.getElementById('productId').value = '';

    if(productId) {
        // Busca o produto específico para garantir dados atualizados
        const product = await fetchData(`api/products.php?id=${productId}`);
        if (product) {
            title.innerText = 'Editar Produto';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productSize').value = product.size;
            document.getElementById('productColor').value = product.color;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            imagePreview.src = product.image || 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
        }
    } else {
        title.innerText = 'Cadastrar Produto';
        imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
    }

    modal.classList.remove('hidden');
}

async function saveProduct(event) {
    event.preventDefault();
    const id = document.getElementById('productId').value;
    const name = document.getElementById('productName').value;
    
    // Simples placeholder para a imagem
    const image = id 
        ? document.getElementById('imagePreview').src 
        : `https://placehold.co/300x300/cccccc/333333?text=${name.replace(/\s/g,'+')}`;

    const productData = {
        id: id ? parseInt(id) : null,
        name: name,
        size: document.getElementById('productSize').value,
        color: document.getElementById('productColor').value,
        price: parseFloat(document.getElementById('productPrice').value),
        stock: parseInt(document.getElementById('productStock').value),
        image: image // Em um sistema real, aqui seria um upload de arquivo
    };

    const result = await postData('api/products.php', productData);

    if (result && result.status === 'success') {
        showAlert(`Produto ${id ? 'atualizado' : 'cadastrado'} com sucesso!`, 'success');
        closeModal('productModal');
        renderProductTable();
        if(document.getElementById('pdv').classList.contains('view')) {
           renderProductList();
        }
    }
}

function deleteProduct(productId) {
    showConfirmationModal('Tem certeza que deseja excluir este produto? Esta ação não pode ser desfeita.', async () => {
         const result = await postData(`api/products.php?id=${productId}`, {}, 'DELETE');
         if (result && result.status === 'success') {
             showAlert('Produto excluído com sucesso!', 'success');
             renderProductTable();
              if(document.getElementById('pdv').classList.contains('view')) {
                renderProductList();
             }
         }
    });
}


// --- DASHBOARD ---

async function updateDashboard() {
    const data = await fetchData('api/dashboard.php');
    if (data) {
        // IDs dos novos cards
        document.getElementById('dailySalesValue').innerText = `R$ ${parseFloat(data.dailySalesValue).toFixed(2).replace('.', ',')}`;
        document.getElementById('monthlySalesValue').innerText = `R$ ${parseFloat(data.monthlySalesValue).toFixed(2).replace('.', ',')}`;
        
        // IDs existentes
        document.getElementById('totalSalesValue').innerText = `R$ ${parseFloat(data.totalSalesValue).toFixed(2).replace('.', ',')}`;
        document.getElementById('totalSalesCount').innerText = data.totalSalesCount;
        document.getElementById('totalStock').innerText = data.totalStock;
    }
}

// --- TABELAS E RELATÓRIOS ---

async function renderProductTable() {
    const tableBody = document.getElementById('productTableBody');
    if (!tableBody) return;

    const products = await fetchData('api/products.php');
    if (!products) {
        tableBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhum produto encontrado.</td></tr>`;
        return;
    }
    
    tableBody.innerHTML = '';
    products.forEach(p => {
        tableBody.innerHTML += `
            <tr class="border-b hover:bg-gray-50 align-middle">
                <td class="p-3 flex items-center gap-3">
                    <img src="${p.image}" alt="${p.name}" class="w-12 h-12 object-cover rounded-md border">
                    <span class="font-medium">${p.name}</span>
                </td>
                <td class="p-3">${p.size}</td>
                <td class="p-3">${p.color}</td>
                <td class="p-3">R$ ${parseFloat(p.price).toFixed(2).replace('.', ',')}</td>
                <td class="p-3 ${p.stock < 5 ? 'text-red-500 font-bold' : ''}">${p.stock}</td>
                <td class="p-3 text-center">
                    <button onclick="openProductModal(${p.id})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteProduct(${p.id})" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
}


async function renderSalesReport(filterType) {
    const reportBody = document.getElementById('salesReportBody');
    if (!reportBody) return;

    currentFilter = filterType;
    let url = 'api/sales.php?';
    
    // Constrói a URL com base nos filtros
    url += `filter=${filterType}`;
    if (filterType === 'custom_day') {
        url += `&date=${document.getElementById('daily-report-date').value}`;
    }
    if (filterType === 'custom_month') {
        url += `&date=${document.getElementById('monthly-report-date').value}`;
    }
    const paymentMethod = document.getElementById('payment-filter').value;
    if (paymentMethod) {
        url += `&payment_method=${paymentMethod}`;
    }
    
    updateFilterButtons(filterType);

    const data = await fetchData(url); 
    if (!data || !data.sales) {
        reportBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhuma venda encontrada para este filtro.</td></tr>`;
        currentFilteredSales = [];
        updateReportSummary({ sales: [], summary: { totalValue: 0, totalCount: 0, byMethod: {} } });
        return;
    }
    
    currentFilteredSales = data.sales;
    reportBody.innerHTML = '';
    
    data.sales.forEach(sale => {
        // Detalhes dos itens (simplificado)
        const itemsSummary = sale.items.map(item => `${item.quantity}x ${item.name}`).join('<br>');

        reportBody.innerHTML += `
            <tr class="border-b hover:bg-gray-50 align-top">
                <td class="p-3 font-medium">#${sale.id}</td>
                <td class="p-3">${new Date(sale.sale_date).toLocaleString('pt-BR')}</td>
                <td class="p-3 text-xs">${itemsSummary}</td>
                <td class="p-3 font-semibold">R$ ${parseFloat(sale.total).toFixed(2).replace('.', ',')}</td>
                <td class="p-3 text-xs font-medium">${sale.payment_methods ? sale.payment_methods.replace(/,/g, ',<br>') : 'N/A'}</td>
                <td class="p-3 text-center">
                    <button onclick="openSaleModal(${sale.id})" class="text-blue-500 hover:text-blue-700 p-2" title="Editar"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteSale(${sale.id})" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `;
    });
    
    updateReportSummary(data);
}

function updateReportSummary(data) {
    const summary = data.summary;
    document.getElementById('summaryTotalValue').innerText = `R$ ${parseFloat(summary.totalValue).toFixed(2).replace('.', ',')}`;
    document.getElementById('summaryTotalCount').innerText = summary.totalCount;

    // Totais por método de pagamento
    document.getElementById('summaryDinheiro').innerText = `R$ ${parseFloat(summary.byMethod['Dinheiro'] || 0).toFixed(2).replace('.', ',')}`;
    document.getElementById('summaryPix').innerText = `R$ ${parseFloat(summary.byMethod['Pix'] || 0).toFixed(2).replace('.', ',')}`;
    document.getElementById('summaryCartao').innerText = `R$ ${parseFloat(summary.byMethod['Cartão'] || 0).toFixed(2).replace('.', ',')}`;
    document.getElementById('summaryLink').innerText = `R$ ${parseFloat(summary.byMethod['Link de Pagamento'] || 0).toFixed(2).replace('.', ',')}`;
}

function updateFilterButtons(activeFilter) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeButton = document.querySelector(`.filter-btn[onclick="renderSalesReport('${activeFilter}')"]`);
    if(activeButton) {
        activeButton.classList.add('active');
    }
}

function clearFilters() {
    document.getElementById('daily-report-date').value = '';
    document.getElementById('monthly-report-date').value = '';
    document.getElementById('payment-filter').value = '';
    renderSalesReport('all');
}

// --- GESTÃO DE VENDAS (NOVO) ---

async function openSaleModal(saleId) {
    const modal = document.getElementById('saleModal');
    const form = document.getElementById('saleForm');
    form.reset();

    const sale = await fetchData(`api/sales.php?id=${saleId}`);
    if (sale) {
        document.getElementById('saleId').value = sale.id;
        // Formata a data para o input datetime-local (YYYY-MM-DDTHH:mm)
        const date = new Date(sale.sale_date);
        const formattedDate = date.getFullYear() + '-' + 
                              ('0' + (date.getMonth() + 1)).slice(-2) + '-' + 
                              ('0' + date.getDate()).slice(-2) + 'T' + 
                              ('0' + date.getHours()).slice(-2) + ':' + 
                              ('0' + date.getMinutes()).slice(-2);
        document.getElementById('saleDate').value = formattedDate;
        modal.classList.remove('hidden');
    }
}

async function saveSale(event) {
    event.preventDefault();
    const saleId = document.getElementById('saleId').value;
    const saleDate = document.getElementById('saleDate').value;

    const saleData = {
        id: parseInt(saleId),
        sale_date: saleDate
    };

    const result = await postData('api/sales.php', saleData, 'PUT');

    if (result && result.status === 'success') {
        showAlert('Data da venda atualizada com sucesso!', 'success');
        closeModal('saleModal');
        renderSalesReport(currentFilter); // Recarrega o relatório
    }
}

function deleteSale(saleId) {
    showConfirmationModal('Tem certeza que deseja excluir esta venda? Esta ação é irreversível e o estoque dos produtos será devolvido.', async () => {
        const result = await postData(`api/sales.php?id=${saleId}`, {}, 'DELETE');
        if (result && result.status === 'success') {
            showAlert('Venda excluída com sucesso!', 'success');
            renderSalesReport(currentFilter); // Recarrega o relatório
        }
    });
}


// --- EXPORTAÇÃO ---
function exportReport(format) {
    if (currentFilteredSales.length === 0) {
        showAlert('Não há dados para exportar.', 'warning');
        return;
    }

    const headers = ["ID Venda", "Data", "Itens", "Total (R$)", "Pagamento"];
    const data = currentFilteredSales.map(sale => ({
        id: sale.id,
        sale_date: new Date(sale.sale_date).toLocaleString('pt-BR'),
        items: sale.items.map(item => `${item.quantity}x ${item.name}`).join(', '),
        total: parseFloat(sale.total).toFixed(2),
        payment_methods: sale.payment_methods
    }));

    if (format === 'pdf') {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.text("Relatório de Vendas", 14, 16);
        doc.autoTable({
            head: [headers],
            body: data.map(Object.values),
            startY: 20,
        });
        doc.save('relatorio_vendas.pdf');

    } else if (format === 'excel') {
        const worksheet = XLSX.utils.json_to_sheet(data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Vendas");
        XLSX.utils.sheet_add_aoa(worksheet, [headers], { origin: "A1" }); // Adiciona cabeçalhos
        
        // Ajusta a largura das colunas
        const max_width = data.reduce((w, r) => Math.max(w, r.items.length), 10);
        worksheet["!cols"] = [ { wch: 10 }, { wch: 20 }, { wch: max_width }, { wch: 15 }, { wch: 25 } ];

        XLSX.writeFile(workbook, "relatorio_vendas.xlsx");
    }
}


// --- FUNÇÕES UTILITÁRIAS (MODAIS, ETC.) ---

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
}

function showConfirmationModal(message, callback) {
    const modal = document.getElementById('confirmationModal');
    document.getElementById('confirmationModalMessage').innerText = message;
    const confirmButton = document.getElementById('confirmActionButton');
    
    // Clona o botão para remover event listeners antigos
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    
    newConfirmButton.addEventListener('click', () => {
        callback();
        closeModal('confirmationModal');
    });
    modal.classList.remove('hidden');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) {
        modal.classList.add('hidden');
    }
}


// --- INICIALIZAÇÃO ---
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'dashboard';
    showView(page);
});