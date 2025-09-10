<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Vendas - Loja de Roupas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="flex h-screen overflow-hidden">

    <aside class="w-20 bg-gray-800 text-white flex flex-col items-center py-6 space-y-8">
        <div class="text-2xl font-bold">
            <i class="fas fa-store text-blue-400" aria-label="Loja"></i>
        </div>
        <nav class="flex flex-col items-center space-y-6">
            <a href="?page=dashboard" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Dashboard" aria-label="Dashboard">
                <i class="fas fa-tachometer-alt"></i>
            </a>
            <a href="?page=pdv" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Ponto de Venda (PDV)" aria-label="PDV">
                <i class="fas fa-cash-register"></i>
            </a>
            <a href="?page=products" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Produtos" aria-label="Produtos">
                <i class="fas fa-tshirt"></i>
            </a>
            <a href="?page=reports" class="sidebar-icon p-3 rounded-lg hover:bg-gray-700" title="Relatórios" aria-label="Relatórios">
                <i class="fas fa-chart-line"></i>
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-6 md:p-8 overflow-y-auto"></main>