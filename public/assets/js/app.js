document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = body.dataset.theme === 'dark' ? 'light' : 'dark';
            body.dataset.theme = current;
            localStorage.setItem('abrm-theme', current);
            fetch('/api/index.php?resource=user-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': themeToggle.dataset.csrf },
                body: JSON.stringify({ theme: current })
            });
        });
        const saved = localStorage.getItem('abrm-theme');
        if (saved) {
            body.dataset.theme = saved;
        }
    }

    const overlay = document.getElementById('search-overlay');
    const overlayInput = document.getElementById('search-input');
    const overlayList = document.getElementById('search-results');
    let openOverlayFn = null;
    if (overlay && overlayInput) {
        const openOverlay = () => {
            overlay.classList.add('open');
            overlayInput.focus();
        };
        openOverlayFn = openOverlay;
        const closeOverlay = () => overlay.classList.remove('open');
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                openOverlay();
            } else if (e.key === 'Escape') {
                closeOverlay();
            }
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeOverlay();
            }
        });
        overlayInput.addEventListener('input', async (e) => {
            const query = e.target.value.trim();
            if (query.length < 2) {
                overlayList.innerHTML = '<li class="muted">Type more…</li>';
                return;
            }
            const response = await fetch(`/api/index.php?resource=search&q=${encodeURIComponent(query)}`);
            const data = await response.json();
            overlayList.innerHTML = '';
            data.data.forEach(result => {
                const li = document.createElement('li');
                li.textContent = `[${result.module}] ${result.title}`;
                overlayList.appendChild(li);
            });
        });
    }
    const searchTrigger = document.getElementById('search-trigger');
    if (searchTrigger && openOverlayFn) {
        searchTrigger.addEventListener('click', () => openOverlayFn());
    }
    const inlineSearch = document.getElementById('global-search');
    if (inlineSearch && openOverlayFn) {
        inlineSearch.addEventListener('focus', () => openOverlayFn());
    }

    document.querySelectorAll('.saved-list li').forEach((node) => {
        node.addEventListener('click', () => {
            const params = JSON.parse(node.dataset.filters || '{}');
            const url = new URL(window.location.href);
            Object.entries(params).forEach(([key, value]) => {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.location.href = url.toString();
        });
    });

    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        const table = document.getElementById('lost-table');
        const bulkState = document.getElementById('bulk-state');
        const releaseBtn = document.getElementById('release-selected');
        const updateSelection = () => {
            const ids = Array.from(table.querySelectorAll('.row-check:checked')).map(cb => cb.closest('tr').dataset.id);
            document.getElementById('state-item-ids').value = ids.join(',');
            document.getElementById('release-item-id').value = ids[0] || '';
            const hasSelection = ids.length > 0;
            bulkState.disabled = !hasSelection;
            releaseBtn.disabled = !hasSelection;
        };
        selectAll.addEventListener('change', () => {
            table.querySelectorAll('.row-check').forEach(cb => cb.checked = selectAll.checked);
            updateSelection();
        });
        table.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateSelection));
    }

    document.querySelectorAll('[data-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.open)?.classList.add('open');
        });
    });
    document.querySelectorAll('[data-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            btn.closest('.modal')?.classList.remove('open');
        });
    });
    document.querySelectorAll('.modal').forEach((modal) => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('open');
            }
        });
    });

    document.querySelectorAll('canvas.signature').forEach((canvas) => {
        const ctx = canvas.getContext('2d');
        let drawing = false;
        const hidden = canvas.parentElement.querySelector(`input[name="${canvas.dataset.target}"]`);
        const resize = () => {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            ctx.strokeStyle = '#111';
            ctx.lineWidth = 2;
        };
        resize();
        const start = (x, y) => {
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(x, y);
        };
        const draw = (x, y) => {
            if (!drawing) return;
            ctx.lineTo(x, y);
            ctx.stroke();
        };
        const stop = () => {
            drawing = false;
            hidden.value = canvas.toDataURL('image/png');
        };
        canvas.addEventListener('mousedown', (e) => start(e.offsetX, e.offsetY));
        canvas.addEventListener('mousemove', (e) => draw(e.offsetX, e.offsetY));
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('mouseleave', stop);
        canvas.addEventListener('touchstart', (e) => {
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            start(touch.clientX - rect.left, touch.clientY - rect.top);
        });
        canvas.addEventListener('touchmove', (e) => {
            const rect = canvas.getBoundingClientRect();
            const touch = e.touches[0];
            draw(touch.clientX - rect.left, touch.clientY - rect.top);
        });
        canvas.addEventListener('touchend', stop);
        window.addEventListener('resize', resize);
    });

    const templateSelect = document.getElementById('template-select');
    if (templateSelect) {
        templateSelect.addEventListener('change', () => {
            if (!templateSelect.value) return;
            const bodyField = document.getElementById('note-body');
            if (bodyField) {
                bodyField.value = templateSelect.selectedOptions[0].dataset.body || '';
            }
            const checklistField = document.querySelector('textarea[name="checklist"]');
            if (checklistField) {
                const list = JSON.parse(templateSelect.selectedOptions[0].dataset.checklist || '[]');
                checklistField.value = list.join('\n');
            }
        });
    }

    const releaseBtn = document.getElementById('release-selected');
    if (releaseBtn) {
        releaseBtn.addEventListener('click', () => {
            const modal = document.getElementById('release-modal');
            modal?.classList.add('open');
        });
    }

    const notificationBell = document.getElementById('notification-bell');
    if (notificationBell) {
        const list = document.getElementById('notification-list');
        const count = notificationBell.querySelector('.badge');
        const fetchNotifications = async () => {
            const res = await fetch('/api/index.php?resource=notifications');
            const data = await res.json();
            if (Array.isArray(data.data)) {
                list.innerHTML = '';
                data.data.forEach(n => {
                    const li = document.createElement('li');
                    li.textContent = n.message;
                    list.appendChild(li);
                });
            }
            count.textContent = data.meta?.unread ?? '0';
        };
        fetchNotifications();
        setInterval(fetchNotifications, 30000);
    }
});
