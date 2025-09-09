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

let currentSalePayments = [];
let currentSaleTotal = 0;
let currentSaleRemaining = 0;

const views = document.querySelectorAll('.view');
const sidebarIcons = document.querySelectorAll('.sidebar-icon');
let currentFilter = 'all';

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

    if (viewId === 'dashboard') updateDashboard();
    if (viewId === 'products') renderProductTable();
    if (viewId === 'reports') renderSalesReport('all');
    if (viewId === 'pdv') renderProductList();
}

function renderProductList() {
    const productList = document.getElementById('productList');
    if (!productList) return;
    
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
    setTimeout(() => {
        const transformElement = modal.querySelector('.transform');
        if (transformElement) {
            transformElement.classList.add('scale-100');
        }
    }, 10);
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
                </div>
            `;
        });
    }

    const confirmButton = document.getElementById('confirmSplitSaleButton');
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

    cart.forEach(cartItem => {
        const product = products.find(p => p.id === cartItem.id);
        if (product) {
            product.stock -= cartItem.quantity;
        }
    });

    cart = [];
    updateCart();
    renderProductList();
    closeModal('paymentSplitModal');
    showAlert('Venda finalizada com sucesso!', 'success');
}

function cancelSplitPayment() {
    closeModal('paymentSplitModal');
}

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
        imagePreview.src = product.image;
    } else {
        title.innerText = 'Cadastrar Produto';
        imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        const transformElement = modal.querySelector('.transform');
        if (transformElement) {
            transformElement.classList.add('scale-100');
        }
    }, 10);
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
        image: ''
    };

    if (file) {
        productData.image = URL.createObjectURL(file);
    } else if (id) {
        productData.image = products.find(p => p.id == id).image;
    } else {
        productData.image = `https://placehold.co/300x300/cccccc/333333?text=${productData.name.replace(/\s/g,'+')}`;
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
    if (!tableBody) return;
    
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

function openSaleModal(saleId) {
    const modal = document.getElementById('saleModal');
    const sale = sales.find(s => s.id === saleId);

    if (sale) {
        document.getElementById('saleId').value = sale.id;
        const saleDate = new Date(sale.date);
        const formattedDate = `${saleDate.getFullYear()}-${String(saleDate.getMonth() + 1).padStart(2, '0')}-${String(saleDate.getDate()).padStart(2, '0')}T${String(saleDate.getHours()).padStart(2, '0')}:${String(saleDate.getMinutes()).padStart(2, '0')}`;
        document.getElementById('saleDate').value = formattedDate;

        modal.classList.remove('hidden');
        setTimeout(() => {
            const transformElement = modal.querySelector('.transform');
            if (transformElement) {
                transformElement.classList.add('scale-100');
            }
        }, 10);
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
            renderProductList();
        }
    });
}

function clearFilters() {
    document.getElementById('daily-report-date').value = '';
    document.getElementById('monthly-report-date').value = '';
    document.getElementById('payment-filter').value = '';
    renderSalesReport('all');
}

function renderSalesReport(filterType) {
    if (filterType !== 'custom_day' && filterType !== 'custom_month') {
         currentFilter = filterType;
    }

    const reportBody = document.getElementById('salesReportBody');
    if (!reportBody) return;
    
    const paymentFilter = document.getElementById('payment-filter').value;
    let filteredSalesSource = sales;
    const now = new Date();

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
                currentFilter = 'all';
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
                currentFilter = 'all';
            }
            break;
        case 'all':
        default:
             if (document.getElementById('daily-report-date').value) renderSalesReport('custom_day');
             else if (document.getElementById('monthly-report-date').value) renderSalesReport('custom_month');
             else filteredSalesSource = sales;
            break;
    }

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

    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    const activeButton = document.querySelector(`.filter-btn[onclick="renderSalesReport('${currentFilter}')"]`);
    if (activeButton) activeButton.classList.add('active');

    reportBody.innerHTML = '';
    if (currentFilteredSales.length === 0) {
         reportBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhuma venda encontrada para os filtros selecionados.</td></tr>`;
    } else {
        currentFilteredSales.slice().reverse().forEach(sale => {
            const itemsSummary = sale.items.map(i => `${i.quantity}x ${i.name.substring(0, 15)}...`).join('<br>');
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
    setTimeout(() => {
        const transformElement = modal.querySelector('.transform');
        if (transformElement) {
            transformElement.classList.add('scale-100');
        }
    }, 10);
}

function showConfirmationModal(message, callback) {
    const modal = document.getElementById('confirmationModal');
    document.getElementById('confirmationModalMessage').innerText = message;
    const confirmButton = document.getElementById('confirmActionButton');
    const newConfirmButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(newConfirmButton, confirmButton);
    newConfirmButton.onclick = () => { callback(); closeModal('confirmationModal'); };
    modal.classList.remove('hidden');
    setTimeout(() => {
        const transformElement = modal.querySelector('.transform');
        if (transformElement) {
            transformElement.classList.add('scale-100');
        }
    }, 10);
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const transformElement = modal.querySelector('.transform');
    if (transformElement) {
        transformElement.classList.remove('scale-100');
    }
    setTimeout(() => modal.classList.add('hidden'), 200);
}

// --- INICIALIZAÇÃO ---
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se há um parâmetro de página na URL
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'dashboard';
    
    showView(page);
});