/**
 * Booking Modal Handler
 * Xử lý logic hiển thị modal đặt sân, sửa booking và hủy booking
 */
class BookingModal {
    constructor(fieldId, today) {
        this.fieldId = fieldId;
        this.today = today;
        this.selectedSlots = [];
        this.isEditMode = false;
        this.editingBookingId = null;
        
        // DOM elements - Booking Modal
        this.bookingModal = document.getElementById('booking-modal');
        this.modalForm = document.getElementById('modal-form');
        this.modalTitle = document.getElementById('modal-title');
        this.customerNameInput = document.getElementById('customer_name');
        this.customerPhoneInput = document.getElementById('customer_phone');
        this.bookingForm = document.getElementById('booking-form');
        this.modalCancelBtn = document.getElementById('modal-cancel');
        this.modalSubmitBtn = document.getElementById('modal-submit');
        this.bookingIdInput = document.getElementById('booking_id');
        this.isEditInput = document.getElementById('is_edit');
        
        // DOM elements - Cancel Modal
        this.cancelModal = document.getElementById('cancel-modal');
        this.cancelSlotSpan = document.getElementById('cancel-slot');
        this.cancelCustomerName = document.getElementById('cancel-customer-name');
        this.cancelCustomerPhone = document.getElementById('cancel-customer-phone');
        this.cancelModalCancel = document.getElementById('cancel-modal-cancel');
        this.cancelModalConfirm = document.getElementById('cancel-modal-confirm');
        
        // State for cancel operation
        this.cancelingBookingId = null;
        
        this.init();
    }
    
    /**
     * Khởi tạo event listeners
     */
    init() {
        this.attachEventListeners();
        this.setupModalKeyboardHandling();
    }
    
    /**
     * Gắn các event listener
     */
    attachEventListeners() {
        // Xử lý submit form đặt sân chính
        this.bookingForm.addEventListener('submit', (e) => {
            this.handleBookingFormSubmit(e);
        });
        
        // Xử lý nút hủy modal booking
        this.modalCancelBtn.addEventListener('click', () => {
            this.closeBookingModal();
        });
        
        // Xử lý submit modal form
        this.modalForm.addEventListener('submit', (e) => {
            this.handleModalFormSubmit(e);
        });
        
        // Đóng booking modal khi click outside
        this.bookingModal.addEventListener('click', (e) => {
            if (e.target === this.bookingModal) {
                this.closeBookingModal();
            }
        });
        
        // Event listeners cho nút Edit
        this.attachEditButtonListeners();
        
        // Event listeners cho nút Delete
        this.attachDeleteButtonListeners();
        
        // Event listeners cho Cancel Modal
        this.attachCancelModalListeners();
    }
    
    /**
     * Gắn event listeners cho nút Edit
     */
    attachEditButtonListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-edit')) {
                e.preventDefault();
                this.handleEditBooking(e.target);
            }
        });
    }
    
    /**
     * Gắn event listeners cho nút Delete
     */
    attachDeleteButtonListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-delete')) {
                e.preventDefault();
                this.handleDeleteBooking(e.target);
            }
        });
    }
    
    /**
     * Gắn event listeners cho Cancel Modal
     */
    attachCancelModalListeners() {
        // Nút "Không" trong cancel modal
        this.cancelModalCancel.addEventListener('click', () => {
            this.closeCancelModal();
        });
        
        // Nút "Có, hủy đặt" trong cancel modal
        this.cancelModalConfirm.addEventListener('click', () => {
            this.confirmDeleteBooking();
        });
        
        // Đóng cancel modal khi click outside
        this.cancelModal.addEventListener('click', (e) => {
            if (e.target === this.cancelModal) {
                this.closeCancelModal();
            }
        });
    }
    
    /**
     * Thiết lập xử lý phím tắt cho modal
     */
    setupModalKeyboardHandling() {
        document.addEventListener('keydown', (e) => {
            if (this.isBookingModalOpen() || this.isCancelModalOpen()) {
                if (e.key === 'Escape') {
                    if (this.isBookingModalOpen()) {
                        this.closeBookingModal();
                    }
                    if (this.isCancelModalOpen()) {
                        this.closeCancelModal();
                    }
                } else if (e.key === 'Enter' && e.ctrlKey && this.isBookingModalOpen()) {
                    // Ctrl + Enter để submit nhanh
                    this.modalForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    }
    
    /**
     * Xử lý Edit booking
     */
    handleEditBooking(button) {
        const bookingId = button.getAttribute('data-booking-id');
        const slot = button.getAttribute('data-slot');
        const customerName = button.getAttribute('data-name');
        const customerPhone = button.getAttribute('data-phone');
        
        this.isEditMode = true;
        this.editingBookingId = bookingId;
        
        // Cập nhật modal title
        this.modalTitle.textContent = 'Sửa thông tin đặt sân';
        this.modalSubmitBtn.textContent = 'Cập nhật';
        
        // Điền thông tin vào form
        this.customerNameInput.value = customerName;
        this.customerPhoneInput.value = customerPhone;
        this.bookingIdInput.value = bookingId;
        this.isEditInput.value = 'true';
        
        // Hiển thị modal
        this.showBookingModal();
    }
    
    /**
     * Xử lý Delete booking
     */
    handleDeleteBooking(button) {
        const bookingId = button.getAttribute('data-booking-id');
        const slot = button.getAttribute('data-slot');
        
        // Tìm thông tin booking từ DOM
        const bookingContainer = button.closest('.booked-slot-container');
        const tooltiptext = bookingContainer.querySelector('.tooltiptext');
        
        let customerName = '';
        let customerPhone = '';
        
        if (tooltiptext) {
            const tooltipContent = tooltiptext.innerHTML;
            const nameMatch = tooltipContent.match(/Người đặt:\s*([^<]*)/);
            const phoneMatch = tooltipContent.match(/SĐT:\s*([^<]*)/);
            
            customerName = nameMatch ? nameMatch[1].trim() : '';
            customerPhone = phoneMatch ? phoneMatch[1].trim() : '';
        }
        
        this.cancelingBookingId = bookingId;
        
        // Cập nhật thông tin trong cancel modal
        this.cancelSlotSpan.textContent = slot;
        this.cancelCustomerName.textContent = customerName;
        this.cancelCustomerPhone.textContent = customerPhone;
        
        // Hiển thị cancel modal
        this.showCancelModal();
    }
    
    /**
     * Xác nhận xóa booking
     */
    async confirmDeleteBooking() {
        if (!this.cancelingBookingId) {
            return;
        }
        
        try {
            this.setCancelModalLoading(true);
            
            const requestData = {
                action: 'delete',
                booking_id: this.cancelingBookingId
            };
            
            const response = await fetch('save_booking.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleDeleteSuccess();
            } else {
                this.handleDeleteError(data.message || 'Hủy đặt sân thất bại');
            }
            
        } catch (error) {
            console.error('Delete booking error:', error);
            this.handleDeleteError('Có lỗi xảy ra. Vui lòng thử lại sau.');
        } finally {
            this.setCancelModalLoading(false);
        }
    }
    
    /**
     * Xử lý khi xóa booking thành công
     */
    handleDeleteSuccess() {
        this.showAlert('Hủy đặt sân thành công!', 'success');
        this.closeCancelModal();
        this.removeBookingFromUI(this.cancelingBookingId);
        this.cancelingBookingId = null;
    }
    
    /**
     * Xử lý khi xóa booking thất bại
     */
    handleDeleteError(message) {
        this.showAlert(message, 'error');
    }
    
    /**
     * Xóa booking khỏi UI
     */
    removeBookingFromUI(bookingId) {
        const editButton = document.querySelector(`[data-booking-id="${bookingId}"].btn-edit`);
        if (editButton) {
            const bookingContainer = editButton.closest('.booked-slot-container');
            if (bookingContainer) {
                const label = bookingContainer.querySelector('label');
                const slot = label.getAttribute('data-slot') || editButton.getAttribute('data-slot');
                
                // Chuyển về dạng checkbox bình thường
                bookingContainer.outerHTML = `
                    <label>
                        <input type="checkbox" name="slots[]" value="${this.escapeHtml(slot)}">
                        ${this.escapeHtml(slot)}
                    </label>
                `;
            }
        }
    }
    
    /**
     * Xử lý submit form đặt sân chính
     */
    handleBookingFormSubmit(e) {
        e.preventDefault();
        
        // Lấy tất cả checkbox đã chọn
        const checkedBoxes = this.bookingForm.querySelectorAll('input[name="slots[]"]:checked');
        
        if (checkedBoxes.length === 0) {
            this.showAlert('Vui lòng chọn ít nhất 1 khung giờ chưa đặt.', 'error');
            return;
        }
        
        // Lưu lại các slot đã chọn
        this.selectedSlots = Array.from(checkedBoxes).map(cb => cb.value);
        
        // Reset edit mode
        this.isEditMode = false;
        this.editingBookingId = null;
        
        // Hiện modal
        this.showBookingModal();
    }
    
    /**
     * Hiển thị booking modal
     */
    showBookingModal() {
        if (!this.isEditMode) {
            // Reset form cho booking mới
            this.resetBookingModalForm();
            this.modalTitle.textContent = 'Thông tin người đặt';
            this.modalSubmitBtn.textContent = 'Xác nhận';
        }
        
        // Hiển thị modal
        this.bookingModal.style.display = 'flex';
        
        // Focus vào input đầu tiên
        setTimeout(() => {
            this.customerNameInput.focus();
        }, 100);
    }
    
    /**
     * Hiển thị cancel modal
     */
    showCancelModal() {
        this.cancelModal.style.display = 'flex';
    }
    
    /**
     * Đóng booking modal
     */
    closeBookingModal() {
        this.bookingModal.style.display = 'none';
        this.resetBookingModalForm();
        this.isEditMode = false;
        this.editingBookingId = null;
    }
    
    /**
     * Đóng cancel modal
     */
    closeCancelModal() {
        this.cancelModal.style.display = 'none';
        this.cancelingBookingId = null;
    }
    
    /**
     * Check booking modal có đang mở không
     */
    isBookingModalOpen() {
        return this.bookingModal.style.display === 'flex';
    }
    
    /**
     * Check cancel modal có đang mở không
     */
    isCancelModalOpen() {
        return this.cancelModal.style.display === 'flex';
    }
    
    /**
     * Reset form trong booking modal
     */
    resetBookingModalForm() {
        this.customerNameInput.value = '';
        this.customerPhoneInput.value = '';
        this.bookingIdInput.value = '';
        this.isEditInput.value = 'false';
        this.clearFormErrors();
    }
    
    /**
     * Xử lý submit modal form
     */
    handleModalFormSubmit(e) {
        e.preventDefault();
        
        if (!this.validateModalForm()) {
            return;
        }
        
        const customerData = this.getCustomerData();
        
        if (this.isEditMode) {
            this.submitUpdateBooking(customerData);
        } else {
            this.submitBooking(customerData);
        }
    }
    
    /**
     * Validate form modal
     */
    validateModalForm() {
        const name = this.customerNameInput.value.trim();
        const phone = this.customerPhoneInput.value.trim();
        
        this.clearFormErrors();
        
        let isValid = true;
        
        if (!name) {
            this.showFieldError(this.customerNameInput, 'Vui lòng nhập họ tên');
            isValid = false;
        } else if (name.length < 2) {
            this.showFieldError(this.customerNameInput, 'Họ tên phải có ít nhất 2 ký tự');
            isValid = false;
        }
        
        if (!phone) {
            this.showFieldError(this.customerPhoneInput, 'Vui lòng nhập số điện thoại');
            isValid = false;
        } else if (!/^[0-9]{9,15}$/.test(phone)) {
            this.showFieldError(this.customerPhoneInput, 'Số điện thoại không hợp lệ (9-15 số)');
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Lấy dữ liệu khách hàng từ form
     */
    getCustomerData() {
        return {
            name: this.customerNameInput.value.trim(),
            phone: this.customerPhoneInput.value.trim()
        };
    }
    
    /**
     * Gửi request đặt sân mới
     */
    async submitBooking(customerData) {
        try {
            this.setBookingModalLoading(true);
            
            const requestData = {
                action: 'create',
                field_id: this.fieldId,
                date: this.today,
                slots: this.selectedSlots,
                customer_name: customerData.name,
                customer_phone: customerData.phone
            };
            
            const response = await fetch('save_booking.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleBookingSuccess(customerData);
            } else {
                this.handleBookingError(data.message || 'Đặt sân thất bại');
            }
            
        } catch (error) {
            console.error('Booking error:', error);
            this.handleBookingError('Có lỗi xảy ra. Vui lòng thử lại sau.');
        } finally {
            this.setBookingModalLoading(false);
        }
    }
    
    /**
     * Gửi request cập nhật booking
     */
    async submitUpdateBooking(customerData) {
        try {
            this.setBookingModalLoading(true);
            
            const requestData = {
                action: 'update',
                booking_id: this.editingBookingId,
                customer_name: customerData.name,
                customer_phone: customerData.phone
            };
            
            const response = await fetch('save_booking.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.handleUpdateSuccess(customerData);
            } else {
                this.handleUpdateError(data.message || 'Cập nhật thông tin thất bại');
            }
            
        } catch (error) {
            console.error('Update booking error:', error);
            this.handleUpdateError('Có lỗi xảy ra. Vui lòng thử lại sau.');
        } finally {
            this.setBookingModalLoading(false);
        }
    }
    
    /**
     * Xử lý khi đặt sân thành công
     */
    handleBookingSuccess(customerData) {
        this.showAlert('Đặt sân thành công!', 'success');
        this.closeBookingModal();
        this.updateUIAfterBooking(customerData);
        this.clearSelectedCheckboxes();
    }
    
    /**
     * Xử lý khi cập nhật booking thành công
     */
    handleUpdateSuccess(customerData) {
        this.showAlert('Cập nhật thông tin thành công!', 'success');
        this.closeBookingModal();
        this.updateUIAfterUpdate(customerData);
    }
    
    /**
     * Xử lý khi đặt sân thất bại
     */
    handleBookingError(message) {
        this.showAlert(message, 'error');
    }
    
    /**
     * Xử lý khi cập nhật booking thất bại
     */
    handleUpdateError(message) {
        this.showAlert(message, 'error');
    }
    
    /**
     * Cập nhật UI sau khi đặt sân thành công
     */
    updateUIAfterBooking(customerData) {
        this.selectedSlots.forEach(slot => {
            const label = this.findSlotLabel(slot);
            if (label) {
                this.markSlotAsBooked(label, slot, customerData);
            }
        });
    }
    
    /**
     * Cập nhật UI sau khi update booking thành công
     */
    updateUIAfterUpdate(customerData) {
        const editButton = document.querySelector(`[data-booking-id="${this.editingBookingId}"].btn-edit`);
        if (editButton) {
            // Cập nhật data attributes
            editButton.setAttribute('data-name', customerData.name);
            editButton.setAttribute('data-phone', customerData.phone);
            
            // Cập nhật tooltiptext
            const bookingContainer = editButton.closest('.booked-slot-container');
            const tooltiptext = bookingContainer.querySelector('.tooltiptext');
            if (tooltiptext) {
                tooltiptext.innerHTML = `
                    Người đặt: ${this.escapeHtml(customerData.name)}<br>
                    SĐT: ${this.escapeHtml(customerData.phone)}
                `;
            }
            
            // Cập nhật data cho delete button cũng
            const deleteButton = bookingContainer.querySelector('.btn-delete');
            if (deleteButton) {
                deleteButton.setAttribute('data-name', customerData.name);
                deleteButton.setAttribute('data-phone', customerData.phone);
            }
        }
    }
    
    /**
     * Tìm label của slot
     */
    findSlotLabel(slot) {
        return Array.from(document.querySelectorAll('.time-slots label'))
            .find(lbl => {
                const checkbox = lbl.querySelector('input[type="checkbox"]');
                return checkbox && checkbox.value === slot;
            });
    }
    
    /**
     * Đánh dấu slot đã được đặt
     */
    markSlotAsBooked(label, slot, customerData) {
        // Tạo một booking ID tạm (trong thực tế sẽ lấy từ response)
        const tempBookingId = Date.now();
        
        label.outerHTML = `
            <div class="booked-slot-container">
                <label class="booked tooltip" data-slot="${this.escapeHtml(slot)}">
                    <span>${this.escapeHtml(slot)}</span>
                    <span class="tooltiptext">
                        Người đặt: ${this.escapeHtml(customerData.name)}<br>
                        SĐT: ${this.escapeHtml(customerData.phone)}
                    </span>
                </label>
                <div class="slot-actions">
                    <button type="button" class="btn-small btn-edit" 
                            data-booking-id="${tempBookingId}"
                            data-slot="${this.escapeHtml(slot)}"
                            data-name="${this.escapeHtml(customerData.name)}"
                            data-phone="${this.escapeHtml(customerData.phone)}">
                        Sửa
                    </button>
                    <button type="button" class="btn-small btn-delete" 
                            data-booking-id="${tempBookingId}"
                            data-slot="${this.escapeHtml(slot)}">
                        Hủy
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Bỏ chọn tất cả checkbox
     */
    clearSelectedCheckboxes() {
        this.bookingForm.querySelectorAll('input[name="slots[]"]:checked')
            .forEach(cb => cb.checked = false);
    }
    
    /**
     * Hiển thị/ẩn loading state cho booking modal
     */
    setBookingModalLoading(isLoading) {
        if (isLoading) {
            this.modalSubmitBtn.disabled = true;
            this.modalSubmitBtn.textContent = 'Đang xử lý...';
            this.modalCancelBtn.disabled = true;
        } else {
            this.modalSubmitBtn.disabled = false;
            this.modalSubmitBtn.textContent = this.isEditMode ? 'Cập nhật' : 'Xác nhận';
            this.modalCancelBtn.disabled = false;
        }
    }
    
    /**
     * Hiển thị/ẩn loading state cho cancel modal
     */
    setCancelModalLoading(isLoading) {
        if (isLoading) {
            this.cancelModalConfirm.disabled = true;
            this.cancelModalConfirm.textContent = 'Đang xử lý...';
            this.cancelModalCancel.disabled = true;
        } else {
            this.cancelModalConfirm.disabled = false;
            this.cancelModalConfirm.textContent = 'Có, hủy đặt';
            this.cancelModalCancel.disabled = false;
        }
    }
    
    /**
     * Hiển thị lỗi cho field cụ thể
     */
    showFieldError(field, message) {
        field.style.borderColor = '#dc3545';
        
        // Xóa error message cũ nếu có
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Thêm error message mới
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '12px';
        errorDiv.style.marginTop = '2px';
        errorDiv.textContent = message;
        
        field.parentNode.appendChild(errorDiv);
    }
    
    /**
     * Xóa tất cả error messages
     */
    clearFormErrors() {
        // Reset border colors
        this.customerNameInput.style.borderColor = '#ccc';
        this.customerPhoneInput.style.borderColor = '#ccc';
        
        // Remove error messages
        this.modalForm.querySelectorAll('.field-error').forEach(error => {
            error.remove();
        });
    }
    
    /**
     * Hiển thị thông báo
     */
    showAlert(message, type = 'info') {
        // Xóa alert cũ nếu có
        const existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        // Tạo alert mới
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        // Style cho alert
        alertDiv.style.cssText = `
            padding: 12px 20px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: 500;
            position: relative;
            ${type === 'success' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : ''}
            ${type === 'error' ? 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : ''}
            ${type === 'info' ? 'background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;' : ''}
        `;
        
        // Thêm vào đầu container
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Scroll to top để thấy alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.style.opacity = '0';
                alertDiv.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }
    
    /**
     * Escape HTML để tránh XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Lấy field_id và today từ PHP (sẽ được inject trong file chính)
    if (typeof BOOKING_CONFIG !== 'undefined') {
        new BookingModal(BOOKING_CONFIG.fieldId, BOOKING_CONFIG.today);
    } else {
        console.error('BOOKING_CONFIG not found. Make sure it is defined in the HTML.');
    }
});