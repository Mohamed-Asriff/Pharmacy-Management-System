
class POSSystem {
    constructor() {
        this.cart = [];
        this.total = 0;
        this.subtotal = 0;
        this.taxRate = 0;
        this.selectedPaymentMethod = 'cash';
        this.lowStockThreshold = 5;
        this.initializePOS();
    }
    
    initializePOS() {
        this.bindEvents();
        this.updateCartDisplay();
        this.loadProducts();
    }
    
    bindEvents() {
       
        document.getElementById('barcodeInput')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.scanProduct();
            }
        });
        
       
        document.getElementById('productSearch')?.addEventListener('input', (e) => {
            this.searchProducts(e.target.value);
        });
        
       
        document.querySelectorAll('.method-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.selectPaymentMethod(e.currentTarget.dataset.method);
            });
        });
        
        
        document.querySelectorAll('.amount-btn[data-amount]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const amount = e.currentTarget.dataset.amount;
                this.setPaidAmount(amount);
            });
        });
        
        document.getElementById('paidAmount')?.addEventListener('input', (e) => {
            this.calculateChange();
        });
    }
    
    loadProducts() {
        this.enhanceStockDisplay();
    }
    
    enhanceStockDisplay() {
        
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            const stockElement = card.querySelector('.product-stock');
            const stockText = stockElement.textContent;
            const stockMatch = stockText.match(/Stock: (\d+)/);
            
            if (stockMatch) {
                const stock = parseInt(stockMatch[1]);
                if (stock === 0) {
                    card.style.opacity = '0.6';
                    const addButton = card.querySelector('.add-to-cart');
                    addButton.disabled = true;
                    addButton.innerHTML = '<i class="fas fa-times"></i> Out of Stock';
                    addButton.classList.remove('btn-primary');
                    addButton.classList.add('btn-secondary');
                } else if (stock <= this.lowStockThreshold) {
                    stockElement.innerHTML = `Stock: <span style="color: var(--warning); font-weight: bold;">${stock} (Low Stock)</span>`;
                }
            }
        });
    }
    
    scanProduct() {
        const barcodeInput = document.getElementById('barcodeInput');
        const barcode = barcodeInput.value.trim();
        const feedback = document.getElementById('barcodeFeedback');
        
        if (!barcode) {
            feedback.innerHTML = '<span style="color: var(--danger);">Please enter a barcode or product code</span>';
            return;
        }
        
        const product = this.findProductByBarcode(barcode);
        
        if (product) {
          
            if (product.stock === 0) {
                feedback.innerHTML = `<span style="color: var(--danger);">Product out of stock: ${product.name}</span>`;
            } else if (product.stock <= this.lowStockThreshold) {
                this.addToCart(product.id);
                feedback.innerHTML = `<span style="color: var(--warning);">Product added (Low Stock: ${product.stock} left): ${product.name}</span>`;
            } else {
                this.addToCart(product.id);
                feedback.innerHTML = `<span style="color: var(--success);">Product added: ${product.name}</span>`;
            }
            barcodeInput.value = '';
        } else {
            feedback.innerHTML = `<span style="color: var(--danger);">Product not found: ${barcode}</span>`;
        }
        
        setTimeout(() => {
            feedback.innerHTML = '';
        }, 3000);
    }
    
    findProductByBarcode(barcode) {
        const products = document.querySelectorAll('.product-card');
        for (let productCard of products) {
            const sku = productCard.querySelector('.product-sku').textContent.replace('SKU: ', '');
            if (sku === barcode) {
                const stockText = productCard.querySelector('.product-stock').textContent;
                const stockMatch = stockText.match(/Stock: (\d+)/);
                const stock = stockMatch ? parseInt(stockMatch[1]) : 0;
                
                return {
                    id: productCard.dataset.productId,
                    name: productCard.querySelector('h4').textContent,
                    price: parseFloat(productCard.querySelector('.price').textContent.replace('Rs. ', '')),
                    sku: sku,
                    stock: stock
                };
            }
        }
        return null;
    }
    
    searchProducts(query) {
        const products = document.querySelectorAll('.product-card');
        const searchTerm = query.toLowerCase();
        
        products.forEach(product => {
            const name = product.querySelector('h4').textContent.toLowerCase();
            const sku = product.querySelector('.product-sku').textContent.toLowerCase();
            const isVisible = name.includes(searchTerm) || sku.includes(searchTerm);
            product.style.display = isVisible ? 'block' : 'none';
        });
    }
    
    addToCart(productId) {
        const productCard = document.querySelector(`.product-card[data-product-id="${productId}"]`);
        if (!productCard) return;
        
        const stockText = productCard.querySelector('.product-stock').textContent;
        const stockMatch = stockText.match(/Stock: (\d+)/);
        const currentStock = stockMatch ? parseInt(stockMatch[1]) : 0;
        
      
        if (currentStock === 0) {
            this.showNotification('This product is out of stock!', 'error');
            return;
        }
        
        const product = {
            id: productId,
            name: productCard.querySelector('h4').textContent,
            price: parseFloat(productCard.querySelector('.price').textContent.replace('Rs. ', '')),
            sku: productCard.querySelector('.product-sku').textContent.replace('SKU: ', ''),
            stock: currentStock
        };
      
        const existingItem = this.cart.find(item => item.id === productId);
        
        if (existingItem) {
         
            if (existingItem.quantity >= product.stock) {
                this.showNotification(`Only ${product.stock} items available in stock!`, 'error');
                return;
            }
            existingItem.quantity++;
        } else {
            this.cart.push({
                ...product,
                quantity: 1
            });
        }
        
        this.updateCartDisplay();
        this.showNotification(`${product.name} added to cart`, 'success');
        
   
        if (product.stock <= this.lowStockThreshold) {
            this.showNotification(`Warning: ${product.name} is low on stock (${product.stock} left)`, 'warning');
        }
    }
    
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.updateCartDisplay();
    }
    
    updateQuantity(productId, change) {
        const item = this.cart.find(item => item.id === productId);
        if (!item) return;
        
        const newQuantity = item.quantity + change;
        
        if (newQuantity < 1) {
            this.removeFromCart(productId);
            return;
        }
        
     
        if (newQuantity > item.stock) {
            this.showNotification(`Only ${item.stock} items available in stock!`, 'error');
            return;
        }
        
        item.quantity = newQuantity;
        this.updateCartDisplay();
    }
    
    updateCartDisplay() {
        const tableBody = document.getElementById('saleTableBody');
        const emptyCart = document.getElementById('emptyCart');
        const itemCount = document.querySelector('.item-count');
        const totalAmount = document.querySelector('.total-amount');
        
       
        this.subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const tax = this.subtotal * (this.taxRate / 100);
        this.total = this.subtotal + tax;
        
       
        itemCount.textContent = `${this.cart.reduce((sum, item) => sum + item.quantity, 0)} items`;
        totalAmount.textContent = `Rs. ${this.total.toFixed(2)}`;
        
      
        document.getElementById('subtotalAmount').textContent = `Rs. ${this.subtotal.toFixed(2)}`;
        document.getElementById('taxAmount').textContent = `Rs. ${tax.toFixed(2)}`;
        document.getElementById('totalAmount').textContent = `Rs. ${this.total.toFixed(2)}`;
        
       
        if (this.cart.length === 0) {
            tableBody.innerHTML = '';
            emptyCart.style.display = 'block';
            return;
        }
        
        emptyCart.style.display = 'none';
        
       
        tableBody.innerHTML = this.cart.map(item => `
            <tr>
                <td>
                    <strong>${item.name}</strong><br>
                    <small class="text-muted">${item.sku}</small>
                </td>
                <td>Rs. ${item.price.toFixed(2)}</td>
                <td>
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="pos.updateQuantity(${item.id}, -1)">-</button>
                        <input type="number" class="qty-input" value="${item.quantity}" min="1" max="${item.stock}" 
                               onchange="pos.updateQuantity(${item.id}, parseInt(this.value) - ${item.quantity})">
                        <button class="qty-btn" onclick="pos.updateQuantity(${item.id}, 1)">+</button>
                    </div>
                </td>
                <td>Rs. ${(item.price * item.quantity).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-danger" onclick="pos.removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.calculateChange();
    }
    
    selectPaymentMethod(method) {
        this.selectedPaymentMethod = method;
        
       
        document.querySelectorAll('.method-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`.method-btn[data-method="${method}"]`).classList.add('active');
    }
    
    setPaidAmount(amount) {
        const paidInput = document.getElementById('paidAmount');
        if (amount === 'exact') {
            paidInput.value = this.total.toFixed(2);
        } else {
            paidInput.value = amount;
        }
        this.calculateChange();
        paidInput.focus();
    }
    
    calculateChange() {
        const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
        const change = paidAmount - this.total;
        document.getElementById('changeAmount').textContent = change >= 0 ? change.toFixed(2) : '0.00';
    }
    
    clearCart() {
        if (this.cart.length === 0) return;
        
        if (confirm('Are you sure you want to clear the cart?')) {
            this.cart = [];
            this.updateCartDisplay();
            this.showNotification('Cart cleared', 'info');
        }
    }
    
    async completeSale() {
        if (this.cart.length === 0) {
            this.showNotification('Cart is empty!', 'error');
            return;
        }
        
        const paidAmount = parseFloat(document.getElementById('paidAmount').value) || 0;
        if (paidAmount < this.total) {
            this.showNotification('Insufficient payment!', 'error');
            return;
        }
        
   
        const saleData = {
            items: this.cart.map(item => ({
                medicine_id: item.id,
                name: item.name,
                quantity: item.quantity,
                price: item.price,
                subtotal: item.price * item.quantity
            })),
            total_amount: this.total,
            paid_amount: paidAmount,
            change_amount: paidAmount - this.total,
            payment_method: this.selectedPaymentMethod
        };
        
        try {
            this.showNotification('Processing sale...', 'info');
            
          
            const response = await fetch('complete_sale.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(saleData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showNotification(`Sale completed successfully!`);
                
                this.updateProductStockAfterSale();
                
              
                setTimeout(() => {
                    this.cart = [];
                    this.updateCartDisplay();
                    document.getElementById('paidAmount').value = '';
                    this.calculateChange();
                }, 2000);
                
            } else {
                this.showNotification('Error: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error completing sale:', error);
            this.showNotification('Error completing sale. Please try again.', 'error');
        }
    }
    
   
updateProductStockAfterSale() {
    this.cart.forEach(cartItem => {
        const productCard = document.querySelector(`.product-card[data-product-id="${cartItem.id}"]`);
        if (productCard) {
            const stockElement = productCard.querySelector('.product-stock');
            const stockText = stockElement.textContent;
            const stockMatch = stockText.match(/Stock: (\d+)/);
            
            if (stockMatch) {
                const currentStock = parseInt(stockMatch[1]);
                const newStock = currentStock - cartItem.quantity;
                stockElement.textContent = `Stock: ${newStock}`; 
                
                
                const addButton = productCard.querySelector('.add-to-cart');
                if (newStock <= 0) {
                    addButton.disabled = true;
                    addButton.innerHTML = '<i class="fas fa-times"></i> Out of Stock';
                    addButton.classList.remove('btn-primary');
                    addButton.classList.add('btn-secondary');
                }
            }
        }
    });
}
async refreshProductStock() {
    try {
        const response = await fetch('get_products.php');
        const products = await response.json();
        
        products.forEach(product => {
            const productCard = document.querySelector(`.product-card[data-product-id="${product.id}"]`);
            if (productCard) {
                const stockElement = productCard.querySelector('.product-stock');
                const addButton = productCard.querySelector('.add-to-cart');
                
                stockElement.textContent = `Stock: ${product.quantity}`;
               
                if (product.quantity <= 0) {
                    addButton.disabled = true;
                    addButton.innerHTML = '<i class="fas fa-times"></i> Out of Stock';
                    addButton.classList.remove('btn-primary');
                    addButton.classList.add('btn-secondary');
                } else {
                    addButton.disabled = false;
                    addButton.innerHTML = '<i class="fas fa-plus"></i> Add';
                    addButton.classList.remove('btn-secondary');
                    addButton.classList.add('btn-primary');
                }
            }
        });
    } catch (error) {
        console.error('Error refreshing product stock:', error);
    }
}
    showNotification(message, type = 'info') {
       
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${this.getNotificationIcon(type)}"></i>
            <span>${message}</span>
        `;
        
        
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${this.getNotificationColor(type)};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
       
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
    
    getNotificationColor(type) {
        const colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            warning: 'var(--warning)',
            info: 'var(--primary)'
        };
        return colors[type] || 'var(--primary)';
    }
}


let pos;
function initializePOS() {
    pos = new POSSystem();
}


function scanProduct() {
    pos.scanProduct();
}

function addToCart(productId) {
    pos.addToCart(productId);
}

function clearCart() {
    pos.clearCart();
}

function completeSale() {
    pos.completeSale();
}

function setExactAmount() {
    pos.setPaidAmount('exact');
}


const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);