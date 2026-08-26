/**
 * Zeko.app - JavaScript Principal
 * Menu mobile, modale de confirmation, interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // MENU MOBILE
    // ============================================
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.classList.toggle('active');
            
            const spans = this.querySelectorAll('span');
            if (navLinks.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
            } else {
                spans[0].style.transform = 'rotate(0) translate(0, 0)';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'rotate(0) translate(0, 0)';
            }
        });
    }
    
    document.addEventListener('click', function(e) {
        if (navLinks && navLinks.classList.contains('active')) {
            if (!e.target.closest('.navbar') && !e.target.closest('.menu-toggle')) {
                navLinks.classList.remove('active');
                const spans = document.querySelectorAll('.menu-toggle span');
                if (spans.length) {
                    spans[0].style.transform = 'rotate(0) translate(0, 0)';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'rotate(0) translate(0, 0)';
                }
            }
        }
    });
    
    // ============================================
    // MODALE DE CONFIRMATION PERSONNALISÉE
    // ============================================
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalConfirm = document.getElementById('modalConfirm');
    const modalCancel = document.getElementById('modalCancel');
    const modalClose = document.getElementById('modalClose');
    const modalDetail = document.querySelector('.modal-detail');
    
    let pendingAction = null;
    
    window.openModal = function(options) {
        const defaults = {
            title: 'Confirmer',
            message: 'Êtes-vous sûr de vouloir effectuer cette action ?',
            icon: 'warning',
            iconClass: 'fas fa-exclamation-triangle',
            confirmText: 'Confirmer',
            confirmClass: 'btn-danger',
            detail: null,
            onConfirm: null
        };
        
        const config = { ...defaults, ...options };
        
        modalTitle.innerHTML = `<span class="modal-icon ${config.icon}"><i class="${config.iconClass}"></i></span> ${config.title}`;
        modalMessage.textContent = config.message;
        modalConfirm.textContent = config.confirmText;
        modalConfirm.className = `btn ${config.confirmClass}`;
        
        // Re-querier à chaque appel pour éviter les duplications
        let detailEl = document.querySelector('.modal-detail');
        if (config.detail) {
            if (!detailEl) {
                detailEl = document.createElement('div');
                detailEl.className = 'modal-detail';
                document.querySelector('.modal-body').appendChild(detailEl);
            }
            detailEl.textContent = config.detail;
            detailEl.style.display = 'block';
        } else if (detailEl) {
            detailEl.style.display = 'none';
        }
        
        pendingAction = config.onConfirm;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        pendingAction = null;
    }
    
    modalCancel.addEventListener('click', closeModal);
    modalClose.addEventListener('click', closeModal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
    
    modalConfirm.addEventListener('click', function() {
        if (typeof pendingAction === 'function') {
            pendingAction();
        }
        closeModal();
    });
    
    // ============================================
    // ⚠️ SUPPRESSION AVEC MODALE PERSONNALISÉE - CORRIGÉ
    // ============================================
    document.querySelectorAll('.delete-confirm').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const href = this.getAttribute('href');
            const productName = this.getAttribute('data-product-name') || 'ce produit';
            
            openModal({
                title: 'Supprimer le produit',
                message: `Êtes-vous sûr de vouloir supprimer "${productName}" ?`,
                icon: 'danger',
                iconClass: 'fas fa-exclamation-circle',
                confirmText: 'Supprimer',
                confirmClass: 'btn-danger',
                detail: 'Cette action est irréversible. Le fichier sera également supprimé.',
                onConfirm: function() {
                    window.location.href = href;
                }
            });
        });
    });
    
    // ============================================
    // SUPPORT POUR data-confirm GÉNÉRIQUE
    // ============================================
    document.querySelectorAll('[data-confirm]').forEach(function(element) {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            
            const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            const title = this.getAttribute('data-confirm-title') || 'Confirmer';
            const icon = this.getAttribute('data-confirm-icon') || 'warning';
            const confirmText = this.getAttribute('data-confirm-text') || 'Confirmer';
            const detail = this.getAttribute('data-confirm-detail') || null;
            const href = this.getAttribute('href');
            const target = this.getAttribute('data-target') || null;
            const redirect = this.getAttribute('data-redirect') || null;
            
            openModal({
                title: title,
                message: message,
                icon: icon,
                iconClass: icon === 'danger' ? 'fas fa-exclamation-circle' : 
                           icon === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle',
                confirmText: confirmText,
                confirmClass: icon === 'danger' ? 'btn-danger' : 
                             icon === 'warning' ? 'btn-warning' : 'btn-primary',
                detail: detail,
                onConfirm: function() {
                    if (redirect) {
                        window.location.href = redirect;
                    } else if (target) {
                        document.getElementById(target).submit();
                    } else if (href && href !== '#') {
                        window.location.href = href;
                    } else {
                        const form = element.closest('form');
                        if (form) {
                            form.submit();
                        }
                    }
                }
            });
        });
    });
    
    // ============================================
    // MESSAGES FLASH AUTO-FERMETURE
    // ============================================
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(function() {
                msg.remove();
            }, 500);
        }, 5000);
    });
    
    // ============================================
    // VALIDATION DE FORMULAIRE EN TEMPS RÉEL
    // ============================================
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(function(input) {
            input.addEventListener('input', function() {
                validateField(this);
            });
            
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('change', function() {
                validateField(this);
            });
        });
        
        function validateField(field) {
            let error = field.parentElement.querySelector('.field-error');
            if (!error) {
                const parent = field.closest('.form-group');
                if (parent) {
                    error = parent.querySelector('.field-error');
                }
            }
            
            let hasError = false;
            let message = '';
            
            if (error) {
                error.textContent = '';
                error.style.display = 'none';
            }
            
            if (field.hasAttribute('required') && !field.value.trim()) {
                hasError = true;
                message = 'Ce champ est requis.';
            } else if (field.type === 'email' && field.value) {
                if (!isValidEmail(field.value)) {
                    hasError = true;
                    message = 'Veuillez entrer une adresse email valide.';
                }
            } else if (field.type === 'password' && field.value && field.value.length < 6) {
                hasError = true;
                message = 'Le mot de passe doit contenir au moins 6 caractères.';
            } else if (field.hasAttribute('data-match') && field.value) {
                const matchField = document.querySelector(field.getAttribute('data-match'));
                if (matchField && field.value !== matchField.value) {
                    hasError = true;
                    message = 'Les valeurs ne correspondent pas.';
                }
            } else if (field.type === 'number' && field.value) {
                if (isNaN(field.value) || field.value < 0) {
                    hasError = true;
                    message = 'Veuillez entrer un nombre valide.';
                }
            }
            
            if (error) {
                if (hasError) {
                    error.textContent = message;
                    error.style.display = 'block';
                    field.style.borderColor = '#e74c3c';
                } else {
                    error.style.display = 'none';
                    field.style.borderColor = '';
                }
            }
            
            return !hasError;
        }
        
        form.addEventListener('submit', function(e) {
            let valid = true;
            inputs.forEach(function(input) {
                if (!validateField(input)) {
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                const firstError = form.querySelector('.field-error[style*="display: block"]');
                if (firstError) {
                    firstError.closest('.form-group').scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
    
    // ============================================
    // TOGGLE PASSWORD
    // ============================================
    document.querySelectorAll('.toggle-password').forEach(function(button) {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.querySelector('i').className = 'fas fa-eye-slash';
                } else {
                    input.type = 'password';
                    this.querySelector('i').className = 'fas fa-eye';
                }
            }
        });
    });
    
    // ============================================
    // UTILITAIRES
    // ============================================
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    window.truncateText = function(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    };
    
    window.formatPrice = function(price) {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    };
    
    console.log('🚀 Zeko.app chargé avec succès !');
});

