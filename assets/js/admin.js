// filepath: cart-php/assets/js/admin.js
// Admin dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    const addProductForm = document.getElementById('add-product-form');
    const editProductForm = document.getElementById('edit-product-form');

    if (addProductForm) {
        addProductForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(addProductForm);
            fetch('add_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                if (data.includes('Thêm sản phẩm thành công')) {
                    window.location.href = 'products.php';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    if (editProductForm) {
        editProductForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(editProductForm);
            fetch('edit_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                if (data.includes('Cập nhật sản phẩm thành công')) {
                    window.location.href = 'products.php';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    document.querySelectorAll('.delete-product').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                fetch('delete_product.php?id=' + productId)
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    if (data.includes('Đã xóa sản phẩm')) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });

    // Preview image when URL is entered
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('input', function() {
            imagePreview.src = this.value || 'https://via.placeholder.com/150';
            imagePreview.style.display = this.value ? 'block' : 'none';
        });
    }
    
    // Confirm product deletion
    const deleteButtons = document.querySelectorAll('.delete-product');
    if (deleteButtons) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Form validation
    const productForms = document.querySelectorAll('form[action*="product"]');
    if (productForms) {
        productForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                let price = parseFloat(this.querySelector('#price').value);
                if (price <= 0) {
                    alert('Giá sản phẩm phải lớn hơn 0!');
                    e.preventDefault();
                }
            });
        });
    }
});