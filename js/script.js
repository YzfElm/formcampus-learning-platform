/* js/script.js — FormCampus */

document.addEventListener('DOMContentLoaded', () => {

    // ── Mobile Nav Toggle ──────────────────────────────────
    const navToggle = document.getElementById('navToggle');
    const navMenu   = document.getElementById('navMenu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => navMenu.classList.toggle('open'));
    }

    // ── Toast helper (global) ─────────────────────────────
    window.showToast = function(type, message) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const icons = { success: '✅', error: '❌', info: 'ℹ️' };
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<span>${icons[type] || '📢'}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4200);
    };

    // ── Form Validation — Inscription publique ─────────────
    const inscriptionForm = document.getElementById('inscriptionForm');
    if (inscriptionForm) {
        inscriptionForm.addEventListener('submit', e => {
            const errors = [];
            const nom       = document.getElementById('nom');
            const prenom    = document.getElementById('prenom');
            const email     = document.getElementById('email');
            const formation = document.getElementById('id_formation');

            if (!nom?.value.trim())    errors.push('Le nom est obligatoire.');
            if (!prenom?.value.trim()) errors.push('Le prénom est obligatoire.');
            if (!email?.value.trim()) {
                errors.push("L'email est obligatoire.");
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                errors.push("Format d'email invalide.");
            }
            if (!formation?.value) errors.push('Veuillez choisir une formation.');

            if (errors.length > 0) {
                e.preventDefault();
                showToast('error', errors.join(' | '));
            }
        });
    }

    // ── Admin Delete Confirmation ──────────────────────────
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });

    // ── Avatar preview in profile ─────────────────────────
    const avatarInput   = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', () => {
            const file = avatarInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => avatarPreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Inline search filter for admin tables ─────────────
    const adminSearch = document.getElementById('adminSearch');
    if (adminSearch) {
        adminSearch.addEventListener('input', () => {
            const q = adminSearch.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    // ── Progress bar animation on load ─────────────────────
    document.querySelectorAll('.progress-bar[data-value]').forEach(bar => {
        const val = parseInt(bar.dataset.value, 10);
        setTimeout(() => { bar.style.width = val + '%'; }, 200);
    });

    // ── V6 3D Carousel & Morphing Logic ───────────────────
    const carouselSection = document.querySelector('.carousel-section');
    const items = document.querySelectorAll('.carousel-item');

    if (items.length > 0) {
        updateCarousel(0);

        items.forEach((item, index) => {
            item.addEventListener('click', e => {
                if (!item.classList.contains('active')) updateCarousel(index);
            });

            const exploreBtn = item.querySelector('.btn-explore');
            if (exploreBtn) {
                exploreBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    if (item.classList.contains('active')) {
                        expandCard(item);
                    } else {
                        updateCarousel(index);
                        setTimeout(() => expandCard(item), 600);
                    }
                });
            }
        });

        function updateCarousel(activeIndex) {
            items.forEach((item, i) => {
                item.classList.remove('active','prev','next','hide-left','hide-right');
                const diff = i - activeIndex;
                if      (diff ===  0) { item.classList.add('active');     item.style.zIndex = 50; }
                else if (diff === -1) { item.classList.add('prev');       item.style.zIndex = 10; }
                else if (diff ===  1) { item.classList.add('next');       item.style.zIndex = 10; }
                else if (diff  <  -1) { item.classList.add('hide-left');  item.style.zIndex = 1; }
                else                  { item.classList.add('hide-right'); item.style.zIndex = 1; }
            });
        }

        function expandCard(originalItem) {
            const rect  = originalItem.getBoundingClientRect();
            const clone = originalItem.cloneNode(true);
            clone.classList.add('clone-card');
            clone.style.cssText = `position:fixed;top:${rect.top}px;left:${rect.left}px;width:${rect.width}px;height:${rect.height}px;z-index:10000;transition:all .6s cubic-bezier(.2,.8,.2,1);margin:0;transform:none;border-radius:20px;`;
            document.body.appendChild(clone);
            originalItem.style.opacity = '0';
            void clone.offsetWidth;
            clone.classList.add('expanded-clone');
            const closeBtn = clone.querySelector('.close-btn');
            if (closeBtn) {
                closeBtn.style.display = 'flex';
                closeBtn.addEventListener('click', () => closeCard(clone, originalItem, rect));
            }
            document.body.style.overflow = 'hidden';
        }

        function closeCard(clone, originalItem, originalRect) {
            clone.classList.remove('expanded-clone');
            clone.style.cssText = `position:fixed;top:${originalRect.top}px;left:${originalRect.left}px;width:${originalRect.width}px;height:${originalRect.height}px;z-index:10000;transition:all .6s cubic-bezier(.2,.8,.2,1);border-radius:20px;margin:0;transform:none;`;
            setTimeout(() => { clone.remove(); originalItem.style.opacity = '1'; document.body.style.overflow = ''; }, 600);
        }
    }

    // ── Export CSV (client-side fallback) ─────────────────
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', () => {
            window.location.href = 'admin_export.php?format=csv';
        });
    }
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', () => {
            window.location.href = 'admin_export.php?format=pdf';
        });
    }

    // ── Mark notification read on click ────────────────────
    document.querySelectorAll('.notif-item[data-id]').forEach(item => {
        item.addEventListener('click', () => {
            const id = item.dataset.id;
            fetch(`notifications.php?mark_read=${id}`);
            item.classList.remove('unread');
            const dot = item.querySelector('.notif-dot');
            if (dot) dot.style.background = 'var(--text-muted)';
        });
    });

});

/* ============================================================
   Système d'évaluation par étoiles — FormCampus Rating JS
   ============================================================ */

(function () {
    'use strict';

    /**
     * Met à jour l'affichage des étoiles d'un bloc .fc-stars
     * selon la valeur survolée ou sélectionnée.
     */
    function highlightStars(starsEl, value) {
        starsEl.querySelectorAll('.fc-star').forEach(star => {
            const v = parseInt(star.dataset.value, 10);
            star.classList.remove('fc-star--full', 'fc-star--empty', 'fc-star--half', 'fc-star--user', 'fc-star--hover');
            if (v <= value) {
                star.classList.add('fc-star--hover');
                star.textContent = '★';
            } else {
                star.classList.add('fc-star--empty');
                star.textContent = '☆';
            }
        });
    }

    /**
     * Restaure les étoiles à leur état initial (note initiale affichée).
     */
    function restoreStars(starsEl, avg, userNote) {
        starsEl.querySelectorAll('.fc-star').forEach(star => {
            const v = parseInt(star.dataset.value, 10);
            star.classList.remove('fc-star--full', 'fc-star--empty', 'fc-star--half', 'fc-star--user', 'fc-star--hover');
            if (userNote > 0) {
                star.classList.add(v <= userNote ? 'fc-star--user' : 'fc-star--empty');
                star.textContent = v <= userNote ? '★' : '☆';
            } else {
                if (avg >= v)             { star.classList.add('fc-star--full');  star.textContent = '★'; }
                else if (avg >= v - 0.5)  { star.classList.add('fc-star--half');  star.textContent = '☆'; }
                else                      { star.classList.add('fc-star--empty'); star.textContent = '☆'; }
            }
        });
    }

    /**
     * Met à jour le bloc méta texte après un vote réussi.
     */
    function updateMeta(ratingEl, data) {
        const meta = ratingEl.querySelector('.fc-rating-meta');
        if (meta) {
            meta.innerHTML = `<strong>${data.note_moyenne}</strong><span>(${data.nb_evaluations} avis)</span>`;
        }
        const feedback = ratingEl.querySelector('.fc-rating-feedback');
        if (feedback) {
            feedback.textContent = '✓ Note enregistrée !';
            feedback.style.display = 'inline';
            setTimeout(() => { feedback.style.display = 'none'; }, 3000);
        }
    }

    /**
     * Initialise les étoiles interactives sur un élément donné.
     */
    function initRatingBlock(ratingEl) {
        const starsEl    = ratingEl.querySelector('.fc-stars--interactive');
        if (!starsEl) return;

        const formationId = parseInt(ratingEl.dataset.formationId, 10);
        let   userNote    = parseInt(ratingEl.dataset.userNote, 10) || 0;
        let   isLoading   = false;

        // --- Hover ---
        starsEl.querySelectorAll('.fc-star').forEach(star => {
            star.addEventListener('mouseenter', () => {
                if (isLoading) return;
                highlightStars(starsEl, parseInt(star.dataset.value, 10));
            });
        });
        starsEl.addEventListener('mouseleave', () => {
            if (isLoading) return;
            restoreStars(starsEl, parseFloat(starsEl.closest('.fc-rating').dataset.avg || 0), userNote);
        });

        // --- Click ---
        starsEl.querySelectorAll('.fc-star').forEach(star => {
            star.addEventListener('click', () => {
                if (isLoading) return;
                const note = parseInt(star.dataset.value, 10);
                isLoading = true;
                starsEl.style.opacity = '.6';

                const body = new URLSearchParams({ formation_id: formationId, note });

                fetch('rate_formation.php', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:    body.toString(),
                })
                .then(r => r.json())
                .then(data => {
                    isLoading = false;
                    starsEl.style.opacity = '1';
                    if (data.success) {
                        userNote = data.user_note;
                        ratingEl.dataset.userNote = userNote;
                        ratingEl.dataset.avg      = data.note_moyenne;
                        restoreStars(starsEl, data.note_moyenne, userNote);
                        updateMeta(ratingEl, data);
                    } else {
                        alert(data.message || 'Erreur lors de l\'envoi de la note.');
                    }
                })
                .catch(() => {
                    isLoading = false;
                    starsEl.style.opacity = '1';
                    alert('Erreur réseau. Veuillez réessayer.');
                });
            });

            // Accessibilité clavier
            star.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); star.click(); }
            });
        });
    }

    // Initialise tous les blocs rating présents dans la page
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fc-rating').forEach(el => {
            el.dataset.avg = el.querySelector('.fc-rating-meta strong')?.textContent || '0';
            initRatingBlock(el);
        });
    });
})();
