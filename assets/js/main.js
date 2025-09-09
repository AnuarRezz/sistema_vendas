// --- SIMULAÇÃO DE BANCO DE DADOS ---
let products = [
    { id: 101, name: 'Camiseta Básica', size: 'P', color: 'Branca', price: 49.90, stock: 15, image: 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta' },
    { id: 102, name: 'Camiseta Básica', size: 'M', color: 'Branca', price: 49.90, stock: 20, image: 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta' },
    // ... (resto dos produtos)
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
    // ... (resto das vendas)
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
    // ... (resto do seu código JavaScript)
}

// --- INICIALIZAÇÃO ---
document.addEventListener('DOMContentLoaded', () => {
    showView('dashboard');
});