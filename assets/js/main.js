// --- VARIÁVEIS GLOBAIS ---
let cart = [];
let currentFilteredSales = []; // Usado para exportação
let currentSalePayments = [];
let currentSaleTotal = 0;
let currentSaleRemaining = 0;
const views = document.querySelectorAll('.view');
const sidebarIcons = document.querySelectorAll('.sidebar-icon');
let currentFilter = 'all';

// --- NAVEGAÇÃO E EXIBIÇÃO ---

function showView(viewId) {
    views.forEach(view => view.classList.add('hidden'));
    
    // Mostra a view correta ou a de erro
    const viewElement = document.getElementById(viewId);
    if (viewElement) {
        viewElement.classList.remove('hidden');
    } else {
        document.getElementById('error').classList.remove('hidden');
    }

    // Atualiza o ícone ativo na sidebar
    sidebarIcons.forEach(icon => icon.classList.remove('bg-gray-700'));
    const activeIcon = document.querySelector(`a[href="?page=${viewId}"]`);
    if (activeIcon) {
        activeIcon.classList.add('bg-gray-700');
    }

    // Carrega os dados necessários para a view
    switch(viewId) {
        case 'dashboard': updateDashboard(); break;
        case 'products': renderProductTable(); break;
        case 'reports': renderSalesReport('all'); break;
        case 'pdv': renderProductList(); break;
    }
}

// --- PDV (Ponto de Venda) ---

async function renderProductList() {
    const productList = document.getElementById('productList');
    if (!productList) return;

    try {
        const searchTerm = document.getElementById('productSearch').value.toLowerCase();
        productList.innerHTML = ''; // Limpa a lista
        
        const productsCol = collection(db, 'products');
        // Cria a query inicial (ordena por nome)
        const q = query(productsCol, orderBy('name', 'asc'));
        
        const snapshot = await getDocs(q);
        
        let filteredProducts = [];
        snapshot.forEach(doc => {
            const product = { id: doc.id, ...doc.data() };
            // Filtra por estoque e termo de busca
            if (product.stock > 0 &&
                (product.name.toLowerCase().includes(searchTerm) || product.id.toLowerCase().includes(searchTerm))
            ) {
                filteredProducts.push(product);
            }
        });

        if (filteredProducts.length === 0) {
            productList.innerHTML = `<p class="col-span-full text-center text-gray-500">Nenhum produto encontrado.</p>`;
            return;
        }

        filteredProducts.forEach(product => {
            const imageUrl = product.image || 'https://placehold.co/300x300/e0e0e0/777?text=Produto';
            productList.innerHTML += `
                <div onclick='addToCart(${JSON.stringify(product)})' class="border rounded-lg p-3 text-center cursor-pointer hover:shadow-lg hover:border-blue-500 transition-all">
                    <img src="${imageUrl}" alt="${product.name}" class="w-full h-24 object-cover rounded-md mb-2">
                    <p class="font-semibold text-sm text-gray-700">${product.name}</p>
                    <p class="text-xs text-gray-500">${product.size} / ${product.color}</p>
                    <p class="font-bold text-blue-600 mt-1">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</p>
                </div>
            `;
        });
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
        showAlert(`Erro ao carregar produtos: ${error.message}`, 'error');
    }
}

// Funções do carrinho (sem alteração da lógica original)
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
// Funções do modal de pagamento (sem alteração da lógica original)
function openSplitPaymentModal() {
    if (cart.length === 0) {
        showAlert('O carrinho está vazio!', 'warning');
        return;
    }
    currentSaleTotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    currentSalePayments = [];
    updateSplitPaymentModalUI();
    document.getElementById('paymentSplitModal').classList.remove('hidden');
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
    document.getElementById('confirmSplitSaleButton').disabled = Math.abs(currentSaleRemaining) >= 0.01;
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
    currentSalePayments.push({ method: methodInput.value, amount: amount });
    updateSplitPaymentModalUI();
}

function removePaymentPart(index) {
    currentSalePayments.splice(index, 1);
    updateSplitPaymentModalUI();
}

function cancelSplitPayment() {
    closeModal('paymentSplitModal');
    currentSalePayments = []; // Limpa pagamentos
}

// **NOVA LÓGICA DE TRANSAÇÃO (Substitui api/sales.php)**
async function confirmSplitSale() {
    // Prepara os dados da venda
    const saleData = {
        sale_date: Timestamp.now(), // Usa o Timestamp do Firebase
        total: currentSaleTotal,
        payments: currentSalePayments,
        // Salva os itens *dentro* do documento da venda
        items: cart.map(item => ({
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            price_per_unit: item.price
        }))
    };

    try {
        // Usa uma transação do Firestore para garantir consistência
        await runTransaction(db, async (transaction) => {
            // 1. Cria o novo documento de venda
            const salesCol = collection(db, 'sales');
            // addDoc não funciona em transações, então usamos doc(collection)
            const newSaleRef = doc(salesCol); 
            transaction.set(newSaleRef, saleData);

            // 2. Atualiza o estoque de cada produto
            for (const item of cart) {
                const productRef = doc(db, 'products', item.id);
                const productDoc = await transaction.get(productRef);
                
                if (!productDoc.exists()) {
                    throw new Error(`Produto ${item.name} (ID: ${item.id}) não encontrado!`);
                }
                
                const currentStock = productDoc.data().stock;
                const newStock = currentStock - item.quantity;
                
                if (newStock < 0) {
                    throw new Error(`Estoque insuficiente para ${item.name}. Restam ${currentStock}.`);
                }
                
                // Atualiza o estoque na transação
                transaction.update(productRef, { stock: newStock });
            }
        });

        // 3. Se a transação foi bem-sucedida
        cart = [];
        updateCart();
        renderProductList(); // Atualiza a lista de produtos com novo estoque
        closeModal('paymentSplitModal');
        showAlert('Venda finalizada com sucesso!', 'success');

    } catch (error) {
        console.error("Falha ao finalizar venda:", error);
        showAlert(`Erro ao salvar venda: ${error.message}`, 'error');
    }
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
        const existingImage = document.getElementById('existingImage').value;
        imagePreview.src = existingImage || 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
    }
}

// **NOVA LÓGICA (Substitui api/products.php - GET)**
async function openProductModal(productId = null) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('productModalTitle');
    const imagePreview = document.getElementById('imagePreview');
    form.reset();
    document.getElementById('productId').value = '';
    document.getElementById('existingImage').value = '';

    if(productId) {
        // Busca o produto do FIRESTORE
        try {
            const productRef = doc(db, 'products', productId);
            const productSnap = await getDoc(productRef);
            
            if (productSnap.exists()) {
                const product = productSnap.data();
                title.innerText = 'Editar Produto';
                document.getElementById('productId').value = productSnap.id; // Salva o ID
                document.getElementById('productName').value = product.name;
                document.getElementById('productSize').value = product.size;
                document.getElementById('productColor').value = product.color;
                document.getElementById('productPrice').value = product.price;
                document.getElementById('productStock').value = product.stock;
                
                const imageUrl = product.image || 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
                imagePreview.src = imageUrl;
                document.getElementById('existingImage').value = product.image || ''; // Salva a URL da imagem existente
            } else {
                 showAlert('Produto não encontrado.', 'error');
                 return;
            }
        } catch (error) {
            console.error("Erro ao buscar produto:", error);
            showAlert(error.message, 'error');
            return;
        }
    } else {
        title.innerText = 'Cadastrar Produto';
        imagePreview.src = 'https://placehold.co/100x100/e0e0e0/777?text=Imagem';
    }
    modal.classList.remove('hidden');
}

// **NOVA LÓGICA (Substitui api/products.php - POST e Upload)**
async function saveProduct(event) {
    event.preventDefault();
    
    const id = document.getElementById('productId').value;
    const name = document.getElementById('productName').value;
    const imageFile = document.getElementById('productImage').files[0];
    let imageUrl = document.getElementById('existingImage').value; // URL da imagem antiga

    try {
        // 1. Se uma nova imagem foi enviada, faz o upload para o Firebase Storage
        if (imageFile) {
            showAlert('Enviando imagem...', 'info');
            const storagePath = `products/${Date.now()}-${imageFile.name}`;
            const storageRef = ref(storage, storagePath);
            
            await uploadBytes(storageRef, imageFile);
            imageUrl = await getDownloadURL(storageRef); // Pega a nova URL
            closeModal('alertModal'); // Fecha o modal de "Enviando"
        } else if (!id && !imageUrl) {
             // Se for um novo produto sem imagem, usa placeholder
             imageUrl = 'https://placehold.co/300x300/e0e0e0/777?text=' + encodeURIComponent(name);
        }

        // 2. Prepara os dados do produto para o Firestore
        const productData = {
            name: name,
            size: document.getElementById('productSize').value,
            color: document.getElementById('productColor').value,
            price: parseFloat(document.getElementById('productPrice').value),
            stock: parseInt(document.getElementById('productStock').value),
            image: imageUrl
        };

        // 3. Salva no Firestore
        if (id) {
            // Edição (UPDATE)
            const productRef = doc(db, 'products', id);
            await updateDoc(productRef, productData);
            showAlert('Produto atualizado com sucesso!', 'success');
        } else {
            // Cadastro (INSERT)
            const productsCol = collection(db, 'products');
            await addDoc(productsCol, productData);
            showAlert('Produto cadastrado com sucesso!', 'success');
        }

        closeModal('productModal');
        renderProductTable(); // Atualiza a tabela de produtos
        // Se o PDV estiver aberto, atualiza a lista lá também
        if(document.getElementById('pdv').classList.contains('hidden') === false) {
           renderProductList();
        }

    } catch (error) {
        console.error("Falha ao salvar produto:", error);
        showAlert(`Erro ao salvar produto: ${error.message}`, 'error');
    }
}

// **NOVA LÓGICA (Substitui api/products.php - DELETE)**
function deleteProduct(productId) {
    // AVISO: A lógica original do PHP (api/products.php) verificava
    // se o produto estava em 'sale_items'. Fazer isso no client-side
    // é ineficiente (teria que ler todas as vendas).
    // O ideal é usar uma Cloud Function.
    // Por enquanto, faremos uma exclusão simples, mas avisamos o usuário.
    showConfirmationModal('Tem certeza que deseja excluir este produto? (Atenção: A verificação de vendas associadas foi removida nesta versão)', async () => {
         try {
            // TODO: Excluir a imagem do Storage se ela existir
            // (requer salvar o storagePath no documento do produto)
            
            const productRef = doc(db, 'products', productId);
            await deleteDoc(productRef);
            
            showAlert('Produto excluído com sucesso!', 'success');
            renderProductTable();
            if(document.getElementById('pdv').classList.contains('hidden') === false) {
                renderProductList();
             }
         } catch (error) {
             console.error("Erro ao excluir produto:", error);
             showAlert(error.message, 'error');
         }
    });
}


// --- DASHBOARD ---

// **NOVA LÓGICA (Substitui api/dashboard.php)**
// AVISO: Esta função é ineficiente. Ela baixa *todas* as vendas e produtos
// para calcular os totais no cliente. Para apps em produção, use
// Cloud Functions para manter agregados (contadores) no Firestore.
async function updateDashboard() {
    try {
        // 1. Busca todas as vendas
        const salesCol = collection(db, 'sales');
        const salesSnapshot = await getDocs(salesCol);
        
        // 2. Busca todos os produtos
        const productsCol = collection(db, 'products');
        const productsSnapshot = await getDocs(productsCol);

        // 3. Calcula os totais no JavaScript
        let dailySalesValue = 0;
        let monthlySalesValue = 0;
        let totalSalesValue = 0;
        let totalSalesCount = 0;
        let totalStock = 0;
        // Helper function (definida no final do arquivo)
        const salesByMonth = createMonthlyArray(); 

        const today = new Date().setHours(0, 0, 0, 0);
        const thisMonth = new Date().getMonth();
        const thisYear = new Date().getFullYear();

        salesSnapshot.forEach(doc => {
            const sale = doc.data();
            // Converte Timestamp do Firebase para Date
            const saleDate = sale.sale_date.toDate(); 

            totalSalesCount++;
            totalSalesValue += sale.total;

            // Compara o dia (ignorando a hora)
            if (saleDate.setHours(0, 0, 0, 0) === today) {
                dailySalesValue += sale.total;
            }
            
            // Compara ano e mês
            if (saleDate.getFullYear() === thisYear) {
                if (saleDate.getMonth() === thisMonth) {
                    monthlySalesValue += sale.total;
                }
                // Adiciona ao total do mês (índice 1-12)
                const monthIndex = saleDate.getMonth() + 1; 
                salesByMonth[monthIndex] += sale.total;
            }
        });

        productsSnapshot.forEach(doc => {
            totalStock += doc.data().stock;
        });

        // 4. Atualiza a UI
        document.getElementById('dailySalesValue').innerText = `R$ ${dailySalesValue.toFixed(2).replace('.', ',')}`;
        document.getElementById('monthlySalesValue').innerText = `R$ ${monthlySalesValue.toFixed(2).replace('.', ',')}`;
        document.getElementById('totalSalesValue').innerText = `R$ ${totalSalesValue.toFixed(2).replace('.', ',')}`;
        document.getElementById('totalSalesCount').innerText = totalSalesCount;
        document.getElementById('totalStock').innerText = totalStock;

        // Renderiza vendas mensais
        const monthlySalesContainer = document.getElementById('monthlySales');
        if (monthlySalesContainer) {
            monthlySalesContainer.innerHTML = ''; 
            const monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
            for (let i = 1; i <= 12; i++) {
                monthlySalesContainer.innerHTML += `
                    <div class="bg-white p-4 rounded-xl shadow-md">
                        <p class="text-gray-500 font-semibold">${monthNames[i-1]}</p>
                        <p class="text-xl font-bold text-gray-800 mt-2">R$ ${salesByMonth[i].toFixed(2).replace('.', ',')}</p>
                    </div>`;
            }
        }
    } catch (error) {
        console.error("Erro ao atualizar dashboard:", error);
        showAlert(`Erro ao carregar dashboard: ${error.message}`, 'error');
    }
}


// --- TABELAS E RELATÓRIOS ---

// **NOVA LÓGICA (Substitui api/products.php - GET)**
async function renderProductTable() {
    const tableBody = document.getElementById('productTableBody');
    if (!tableBody) return;

    try {
        const productsCol = collection(db, 'products');
        const q = query(productsCol, orderBy('name', 'asc'));
        const snapshot = await getDocs(q);

        if (snapshot.empty) {
            tableBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhum produto encontrado.</td></tr>`;
            return;
        }
        
        tableBody.innerHTML = '';
        snapshot.forEach(doc => {
            const p = doc.data();
            const id = doc.id; // Pega o ID do documento
            const imageUrl = p.image || 'https://placehold.co/100x100/e0e0e0/777?text=Produto';
            
            tableBody.innerHTML += `
                <tr class="border-b hover:bg-gray-50 align-middle">
                    <td class="p-3 flex items-center gap-3">
                        <img src="${imageUrl}" alt="${p.name}" class="w-12 h-12 object-cover rounded-md border">
                        <span class="font-medium">${p.name}</span>
                    </td>
                    <td class="p-3">${p.size}</td>
                    <td class="p-3">${p.color}</td>
                    <td class="p-3">R$ ${parseFloat(p.price).toFixed(2).replace('.', ',')}</td>
                    <td class="p-3 ${p.stock < 5 ? 'text-red-500 font-bold' : ''}">${p.stock}</td>
                    <td class="p-3 text-center">
                        <button onclick="openProductModal('${id}')" class="text-blue-500 hover:text-blue-700 p-2" title="Editar"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteProduct('${id}')" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
        });
    } catch (error) {
        console.error("Erro ao renderizar tabela de produtos:", error);
        showAlert(`Erro ao carregar produtos: ${error.message}`, 'error');
    }
}

// **NOVA LÓGICA (Substitui api/sales.php - GET com filtros)**
async function renderSalesReport(filterType) {
    const reportBody = document.getElementById('salesReportBody');
    if (!reportBody) return;

    currentFilter = filterType;
    let constraints = [orderBy('sale_date', 'desc')]; // Começa ordenando
    
    // Constrói as constraints de data
    const now = new Date();
    const todayStart = new Date(now.setHours(0, 0, 0, 0));
    const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);

    if (filterType === 'today') {
        constraints.push(where('sale_date', '>=', todayStart));
    }
    if (filterType === 'this_month') {
        constraints.push(where('sale_date', '>=', monthStart));
    }
    if (filterType === 'custom_day') {
        const date = document.getElementById('daily-report-date').value;
        if (date) {
            const dayStart = new Date(date + 'T00:00:00'); // Fuso local
            const dayEnd = new Date(date + 'T23:59:59');
            constraints.push(where('sale_date', '>=', dayStart));
            constraints.push(where('sale_date', '<=', dayEnd));
        }
    }
    if (filterType === 'custom_month') {
        const date = document.getElementById('monthly-report-date').value; // Formato YYYY-MM
        if (date) {
            const [year, month] = date.split('-').map(Number);
            const mStart = new Date(year, month - 1, 1);
            const mEnd = new Date(year, month, 0); // Último dia do mês
            mEnd.setHours(23, 59, 59);
            constraints.push(where('sale_date', '>=', mStart));
            constraints.push(where('sale_date', '<=', mEnd));
        }
    }
    
    // Filtro de pagamento
    const paymentMethod = document.getElementById('payment-filter').value;
    if (paymentMethod) {
        // Firestore 'array-contains' é perfeito para isso
        constraints.push(where('payments', 'array-contains-any', [
            { method: paymentMethod, amount: 0.01 } // Isso é um hack, precisamos de um campo de 'payment_methods'
            // A estrutura do 'payments' é [{method: "Dinheiro", amount: 100}]
            // Precisamos de 'array-contains'.
            // A consulta 'array-contains-any' não funciona bem em objetos complexos.
            // SOLUÇÃO: Adicionar um campo 'payment_methods' [Dinheiro, Pix] ao salvar a venda.
            // Por enquanto, este filtro não funcionará.
            console.warn("Filtro de pagamento não implementado. Requer modificação na estrutura de 'sales'.");
        }
    }
    
    updateFilterButtons(filterType);

    try {
        const salesCol = collection(db, 'sales');
        const q = query(salesCol, ...constraints);
        const snapshot = await getDocs(q);

        if (snapshot.empty) {
            reportBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-gray-500">Nenhuma venda encontrada para este filtro.</td></tr>`;
            currentFilteredSales = [];
            updateReportSummary([], { totalValue: 0, totalCount: 0, byMethod: {} });
            return;
        }

        const sales = [];
        snapshot.forEach(doc => {
            sales.push({ id: doc.id, ...doc.data() });
        });
        
        currentFilteredSales = sales; // Salva para exportação
        reportBody.innerHTML = '';
        
        const summaryData = { totalValue: 0, totalCount: 0, byMethod: {} };

        sales.forEach(sale => {
            // Detalhes dos itens
            const itemsSummary = sale.items.map(item => `${item.quantity}x ${item.name}`).join('<br>');
            
            // Métodos de pagamento
            const paymentMethods = sale.payments.map(p => p.method).join(',<br>');

            // Formata data
            const saleDate = sale.sale_date.toDate().toLocaleString('pt-BR');

            reportBody.innerHTML += `
                <tr class="border-b hover:bg-gray-50 align-top">
                    <td class="p-3 font-medium">#${sale.id.substring(0, 5)}...</td>
                    <td class="p-3">${saleDate}</td>
                    <td class="p-3 text-xs">${itemsSummary}</td>
                    <td class="p-3 font-semibold">R$ ${parseFloat(sale.total).toFixed(2).replace('.', ',')}</td>
                    <td class="p-3 text-xs font-medium">${paymentMethods}</td>
                    <td class="p-3 text-center">
                        <button onclick="openSaleModal('${sale.id}')" class="text-blue-500 hover:text-blue-700 p-2" title="Editar Data"><i class="fas fa-edit"></i></button>
                        <button onclick="deleteSale('${sale.id}')" class="text-red-500 hover:text-red-700 p-2" title="Excluir"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            
            // Calcula sumário
            summaryData.totalValue += sale.total;
            summaryData.totalCount++;
            sale.payments.forEach(p => {
                const method = p.method.includes('Cartão') ? 'Cartão' : p.method;
                summaryData.byMethod[method] = (summaryData.byMethod[method] || 0) + p.amount;
            });
        });
        
        updateReportSummary(summaryData);

    } catch (error) {
        console.error("Erro ao gerar relatório:", error);
        showAlert(error.message, 'error');
        reportBody.innerHTML = `<tr><td colspan="6" class="p-4 text-center text-red-500">Erro ao buscar dados.</td></tr>`;
    }
}

function updateReportSummary(summary) {
    document.getElementById('summaryTotalValue').innerText = `R$ ${parseFloat(summary.totalValue).toFixed(2).replace('.', ',')}`;
    document.getElementById('summaryTotalCount').innerText = summary.totalCount;
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


// --- GESTÃO DE VENDAS ---

// **NOVA LÓGICA (Substitui api/sales.php - GET por ID)**
async function openSaleModal(saleId) {
    const modal = document.getElementById('saleModal');
    const form = document.getElementById('saleForm');
    form.reset();

    try {
        const saleRef = doc(db, 'sales', saleId);
        const saleSnap = await getDoc(saleRef);
        
        if (saleSnap.exists()) {
            const sale = saleSnap.data();
            document.getElementById('saleId').value = saleSnap.id;
            
            // Converte Timestamp para formato datetime-local
            const date = sale.sale_date.toDate();
            date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
            const formattedDate = date.toISOString().slice(0, 16);
                                  
            document.getElementById('saleDate').value = formattedDate;
            modal.classList.remove('hidden');
        }
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// **NOVA LÓGICA (Substitui api/sales.php - PUT)**
async function saveSale(event) {
    event.preventDefault();
    const saleId = document.getElementById('saleId').value;
    const saleDate = document.getElementById('saleDate').value; // String 'YYYY-MM-DDTHH:mm'

    try {
        const saleRef = doc(db, 'sales', saleId);
        // Converte a string local para um objeto Date e depois para Timestamp
        const newTimestamp = Timestamp.fromDate(new Date(saleDate));
        
        await updateDoc(saleRef, {
            sale_date: newTimestamp
        });
        
        showAlert('Data da venda atualizada com sucesso!', 'success');
        closeModal('saleModal');
        renderSalesReport(currentFilter); // Recarrega o relatório
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// **NOVA LÓGICA (Substitui api/sales.php - DELETE)**
function deleteSale(saleId) {
    showConfirmationModal('Tem certeza que deseja excluir esta venda? Esta ação é irreversível e o estoque dos produtos será devolvido.', async () => {
        
        try {
            await runTransaction(db, async (transaction) => {
                const saleRef = doc(db, 'sales', saleId);
                const saleDoc = await transaction.get(saleRef);
                
                if (!saleDoc.exists()) {
                    throw new Error("Venda não encontrada.");
                }
                
                const items = saleDoc.data().items;

                // 1. Devolve os itens ao estoque
                for (const item of items) {
                    const productRef = doc(db, 'products', item.id);
                    const productDoc = await transaction.get(productRef);
                    if (productDoc.exists()) {
                        const newStock = productDoc.data().stock + item.quantity;
                        transaction.update(productRef, { stock: newStock });
                    }
                }

                // 2. Exclui a venda
                transaction.delete(saleRef);
            });

            showAlert('Venda excluída e estoque devolvido!', 'success');
            renderSalesReport(currentFilter); // Recarrega o relatório
            updateDashboard(); // Atualiza os totais do dashboard

        } catch (error) {
            console.error("Erro ao excluir venda:", error);
            showAlert(`Erro ao excluir venda: ${error.message}`, 'error');
        }
    });
}


// --- EXPORTAÇÃO (Lógica sem alteração) ---
function exportReport(format) {
    if (currentFilteredSales.length === 0) {
        showAlert('Não há dados para exportar.', 'warning');
        return;
    }

    const headers = ["ID Venda", "Data", "Itens", "Total (R$)", "Pagamento"];
    const data = currentFilteredSales.map(sale => ({
        id: sale.id,
        sale_date: sale.sale_date.toDate().toLocaleString('pt-BR'),
        items: sale.items.map(item => `${item.quantity}x ${item.name}`).join(', '),
        total: parseFloat(sale.total).toFixed(2),
        payment_methods: sale.payments.map(p => p.method).join(', ')
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
        XLSX.utils.sheet_add_aoa(worksheet, [headers], { origin: "A1" });
        const max_width = data.reduce((w, r) => Math.max(w, r.items.length), 10);
        worksheet["!cols"] = [ { wch: 10 }, { wch: 20 }, { wch: max_width }, { wch: 15 }, { wch: 25 } ];
        XLSX.writeFile(workbook, "relatorio_vendas.xlsx");
    }
}


// --- FUNÇÕES UTILITÁRIAS (Modais, etc. - Sem alteração) ---

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
        default: // 'info'
             titleDiv.innerText = 'Aguarde...';
             iconDiv.innerHTML = `<i class="fas fa-spinner fa-spin text-5xl text-blue-500"></i>`; break;
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

// Helper function para o dashboard
function createMonthlyArray() {
    const arr = {};
    for (let i = 1; i <= 12; i++) {
        arr[i] = 0;
    }
    return arr;
}


// --- INICIALIZAÇÃO ---
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'dashboard';
    showView(page);

    // Adiciona listener para navegação (quando clica nos ícones)
    document.querySelectorAll('.sidebar-icon').forEach(icon => {
        icon.addEventListener('click', (e) => {
             // Só age em links de navegação (ignora o logout)
            if (icon.href && icon.href.includes('?page=')) {
                e.preventDefault();
                const urlParams = new URLSearchParams(icon.search);
                const page = urlParams.get('page');
                // Atualiza a URL sem recarregar
                window.history.pushState({}, '', `?page=${page}`);
                showView(page);
            }
        });
    });

    // Listener para o botão 'voltar' do navegador
    window.addEventListener('popstate', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page') || 'dashboard';
        showView(page);
    });
});