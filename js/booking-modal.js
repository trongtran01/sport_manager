/**
 * Booking Modal Handler
 * Xử lý logic hiển thị modal đặt sân và gửi dữ liệu
 */
class BookingModal {
    constructor(fieldId, today) {
        this.fieldId = fieldId;
        this.today = today;
        this.selectedSlots = [];
        
        // DOM elements
        this.bookingModal = document.getElementById('booking-modal');
        this.modalForm = document.getElementById('modal-form');
        this.customerNameInput = document.getElementById('customer_name');
        this.customerPhoneInput = document.getElementById('customer_phone');
        this.bookingForm = document.getElementById('booking-form');
        this.modalCancelBtn = document.getElementById('modal-cancel');
        
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
        
        // Xử lý nút hủy modal
        this.modalCancelBtn.addEventListener('click', () => {
            this.closeModal();
        });
        
        // Xử lý submit modal form
        this.modalForm.addEventListener('submit', (e) => {
            this.handleModalFormSubmit(e);
        });
        
        // Đóng modal khi click outside
        this.bookingModal.addEventListener('click', (e) => {
            if (e.target === this.bookingModal) {
                this.closeModal();
            }
        });
    }
    
    /**
     * Thiết lập xử lý phím tắt cho modal
     */
    setupModalKeyboardHandling() {
        document.addEventListener('keydown', (e) => {
            if (this.isModalOpen()) {
                if (e.key === 'Escape') {
                    this.closeModal();
                } else if (e.key === 'Enter' && e.ctrlKey) {
                    // Ctrl + Enter để submit nhanh
                    this.modalForm.dispatchEvent(new Event('submit'));
                }
            }
        });
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
        
        // Hiện modal
        this.showModal();
    }
    
    /**
     * Hiển thị modal
     */
    showModal() {
        // Reset form
        this.resetModalForm();
        
        // Hiển thị modal
        this.bookingModal.style.display = 'flex';
        
        // Focus vào input đầu tiên
        setTimeout(() => {
            this.customerNameInput.focus();
        }, 100);
    }
    
    /**
     * Đóng modal
     */
    closeModal() {
        this.bookingModal.style.display = 'none';
        this.resetModalForm();
    }
    
    /**
     * Check modal có đang mở không
     */
    isModalOpen() {
        return this.bookingModal.style.display === 'flex';
    }
    
    /**
     * Reset form trong modal
     */
    resetModalForm() {
        this.customerNameInput.value = '';
        this.customerPhoneInput.value = '';
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
        this.submitBooking(customerData);
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
     * Gửi request đặt sân
     */
    async submitBooking(customerData) {
        try {
            this.setModalLoading(true);
            
            const requestData = {
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
            this.setModalLoading(false);
        }
    }
    
    /**
     * Xử lý khi đặt sân thành công
     */
    handleBookingSuccess(customerData) {
        this.showAlert('Đặt sân thành công!', 'success');
        this.closeModal();
        this.updateUIAfterBooking(customerData);
        this.clearSelectedCheckboxes();
    }
    
    /**
     * Xử lý khi đặt sân thất bại
     */
    handleBookingError(message) {
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
        label.classList.add('booked', 'tooltip');
        label.innerHTML = `
            <span>${slot}</span>
            <span class="tooltiptext">
                Người đặt: ${this.escapeHtml(customerData.name)}<br>
                SĐT: ${this.escapeHtml(customerData.phone)}
            </span>
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
     * Hiển thị/ẩn loading state cho modal
     */
    setModalLoading(isLoading) {
        const submitBtn = this.modalForm.querySelector('button[type="submit"]');
        const cancelBtn = this.modalCancelBtn;
        
        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';
            cancelBtn.disabled = true;
        } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Xác nhận';
            cancelBtn.disabled = false;
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
        
        // Thêm vào đầu container
        const container = document.querySelector('.container');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Tự động ẩn sau 5 giây
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
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
    }
});