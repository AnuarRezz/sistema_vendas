<div id="alertModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4" role="dialog" aria-modal="true">
    <div class="bg-white p-8 rounded-xl shadow-2xl text-center max-w-sm w-full transform transition-transform scale-95">
        <div id="modalIcon" class="mb-4"></div>
        <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-2">Atenção</h3>
        <p id="modalMessage" class="text-gray-600 mb-6">Mensagem do modal.</p>
        <button type="button" onclick="closeModal('alertModal')" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors">OK</button>
    </div>
</div>

<div id="confirmationModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4" role="dialog" aria-modal="true">
    <div class="bg-white p-8 rounded-xl shadow-2xl text-center max-w-sm w-full transform transition-transform scale-95">
        <div id="confirmationModalIcon" class="mb-4"><i class="fas fa-question-circle text-5xl text-yellow-500"></i></div>
        <h3 id="confirmationModalTitle" class="text-xl font-bold text-gray-800 mb-2">Confirmar Ação</h3>
        <p id="confirmationModalMessage" class="text-gray-600 mb-6">Tem certeza que deseja continuar?</p>
        <div class="flex justify-center gap-4">
            <button type="button" onclick="closeModal('confirmationModal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar</button>
            <button type="button" id="confirmActionButton" class="bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 transition-colors">Confirmar</button>
        </div>
    </div>
</div>

<div id="productModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4 overflow-auto" role="dialog" aria-modal="true">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg transform transition-transform scale-95 my-auto">
        <h3 id="productModalTitle" class="text-2xl font-bold text-gray-800 mb-6">Cadastrar Produto</h3>
        <form id="productForm" onsubmit="saveProduct(event)">
            <input type="hidden" id="productId">
            <input type="hidden" id="existingImage">
            
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

<div id="saleModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4" role="dialog" aria-modal="true">
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

<div id="paymentSplitModal" class="modal-backdrop fixed inset-0 flex items-center justify-center hidden z-50 p-4 overflow-auto" role="dialog" aria-modal="true">
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
                <button type="button" onclick="addPaymentPart()" class="bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition-colors self-end h-[42px]">Adicionar Pagamento</button>
            </div>
        </div>
        <div class="mb-6">
            <h4 class="font-semibold mb-2">Pagamentos Registrados:</h4>
            <div id="paymentPartsList" class="space-y-2 max-h-32 overflow-y-auto bg-gray-50 p-3 rounded-lg">
                <p class="text-gray-400 text-center italic">Nenhum pagamento adicionado.</p>
            </div>
        </div>
        <div class="flex justify-between gap-4 mt-8">
            <button type="button" onclick="cancelSplitPayment()" class="bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded-lg hover:bg-gray-400 transition-colors">Cancelar Venda</button>
            <button type="button" id="confirmSplitSaleButton" onclick="confirmSplitSale()" class="bg-blue-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>Confirmar Venda</button>
        </div>
    </div>
</div>