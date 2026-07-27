document.addEventListener('DOMContentLoaded', function () {


    /* Табы начало*/
    const tabLists = document.querySelectorAll('[role="tablist"]');

    tabLists.forEach(tabList => {
        const tabs = tabList.querySelectorAll('[role="tab"]');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                activateTab(tab, tabs);
            });

            // Поддержка клавиатуры (стрелки + Enter)
            tab.addEventListener('keydown', (e) => {
                const index = Array.from(tabs).indexOf(tab);

                if (e.key === 'ArrowRight') {
                    tabs[(index + 1) % tabs.length].focus();
                }

                if (e.key === 'ArrowLeft') {
                    tabs[(index - 1 + tabs.length) % tabs.length].focus();
                }

                if (e.key === 'Enter' || e.key === ' ') {
                    activateTab(tab, tabs);
                }
            });
        });
    });

    function activateTab(activeTab, tabs) {
        tabs.forEach(tab => {
            const panelId = tab.getAttribute('aria-controls');
            const panel = document.getElementById(panelId);

            const isActive = tab === activeTab;

            // aria
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.setAttribute('tabindex', isActive ? '0' : '-1');

            // видимость
            panel.hidden = !isActive;

            // 🔥 ВАЖНО: управление disabled
            togglePanelFields(panel, !isActive);
        });

        activeTab.focus();
    }

    function togglePanelFields(panel, disable) {
        const fields = panel.querySelectorAll('input, select, textarea, button');

        fields.forEach(el => {
            // Не трогаем элементы, которые уже были disabled изначально
            if (disable) {
                if (!el.hasAttribute('data-was-disabled')) {
                    if (el.disabled) {
                        el.setAttribute('data-was-disabled', 'true');
                    }
                }
                el.disabled = true;
            } else {
                // Восстанавливаем только те, что мы сами отключили
                if (!el.hasAttribute('data-was-disabled')) {
                    el.disabled = false;
                } else {
                    el.removeAttribute('data-was-disabled');
                }
            }
        });
    }

    // Инициализация
    document.querySelectorAll('[role="tab"]').forEach(tab => {
        const panel = document.getElementById(tab.getAttribute('aria-controls'));
        const isActive = tab.getAttribute('aria-selected') === 'true';

        panel.hidden = !isActive;
        tab.setAttribute('tabindex', isActive ? '0' : '-1');

        // 🔥 применяем disabled при старте
        togglePanelFields(panel, !isActive);
    });

    /* Табы конец */
        
})