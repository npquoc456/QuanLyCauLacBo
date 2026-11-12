const themeKey = 'uniclub-theme';
const fontSizeKey = 'uniclub-font-size';
const themeToggle = document.querySelector('.theme-toggle');
const storedTheme = localStorage.getItem(themeKey);
const storedFontSize = localStorage.getItem(fontSizeKey);

if (storedTheme === 'dark' || storedTheme === 'light') {
    document.documentElement.dataset.theme = storedTheme;
}

const validFontSizes = ['small', 'medium', 'large'];
const activeFontSize = validFontSizes.includes(storedFontSize) ? storedFontSize : 'medium';
document.documentElement.dataset.fontSize = activeFontSize;

const applyFontSizeSelection = (size) => {
    document.querySelectorAll('[data-font-size]').forEach((button) => {
        button.classList.toggle('is-selected', button.dataset.fontSize === size);
    });
};

applyFontSizeSelection(activeFontSize);

document.querySelectorAll('[data-font-size]').forEach((button) => {
    button.addEventListener('click', () => {
        const fontSize = button.dataset.fontSize;
        if (!validFontSizes.includes(fontSize)) return;
        document.documentElement.dataset.fontSize = fontSize;
        localStorage.setItem(fontSizeKey, fontSize);
        applyFontSizeSelection(fontSize);
    });
});

if (themeToggle) {
    const updateThemeLabel = () => {
        const darkMode = document.documentElement.dataset.theme === 'dark';
        themeToggle.setAttribute('aria-label', darkMode ? 'Bật giao diện sáng' : 'Bật giao diện tối');
    };

    updateThemeLabel();
    themeToggle.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = nextTheme;
        localStorage.setItem(themeKey, nextTheme);
        updateThemeLabel();
    });
}

document.querySelectorAll('.notice-close').forEach((button) => {
    button.addEventListener('click', () => button.closest('.notice')?.remove());
});

document.querySelectorAll('[data-profile-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-profile-menu-trigger]');
    const panel = menu.querySelector('[data-profile-menu-panel]');

    if (!trigger || !panel) {
        return;
    }

    const closeMenu = () => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    };

    trigger.addEventListener('click', () => {
        const isOpen = trigger.getAttribute('aria-expanded') === 'true';
        panel.hidden = isOpen;
        trigger.setAttribute('aria-expanded', String(!isOpen));
    });

    document.addEventListener('click', (event) => {
        if (!menu.contains(event.target)) {
            closeMenu();
        }
    });

    menu.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
            trigger.focus();
        }
    });
});

document.querySelectorAll('.event-background-input').forEach((input) => {
    const preview = input.closest('.event-field')?.querySelector('[data-background-preview]');
    const fileName = input.closest('.event-field')?.querySelector('[data-upload-filename]');

    input.addEventListener('change', () => {
        const image = input.files?.[0];

        if (image && fileName) {
            fileName.textContent = image.name;
        }

        if (!image || !preview) {
            return;
        }

        preview.src = URL.createObjectURL(image);
        preview.hidden = false;
    });
});

const eventCheckboxes = [...document.querySelectorAll('[data-event-checkbox]')];
const selectAll = document.querySelector('[data-select-all]');
const bulkForm = document.querySelector('#bulk-event-form');
const selectedCount = document.querySelector('[data-selected-count]');

const updateSelection = () => {
    const selected = eventCheckboxes.filter((checkbox) => checkbox.checked).length;

    if (selectedCount && bulkForm) {
        selectedCount.textContent = `Đã chọn ${selected}`;
        bulkForm.classList.toggle('has-selection', selected > 0);
    }

    if (selectAll) {
        selectAll.checked = eventCheckboxes.length > 0 && selected === eventCheckboxes.length;
        selectAll.indeterminate = selected > 0 && selected < eventCheckboxes.length;
    }
};

eventCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
selectAll?.addEventListener('change', () => {
    eventCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
    });
    updateSelection();
});

document.querySelectorAll('[data-open-club-create]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const dialog = document.querySelector('#club-create-modal');
        dialog?.showModal();
        dialog?.querySelector('input[name="TenCLB"]')?.focus();
    });
});

document.querySelectorAll('[data-open-club-edit]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        const dialog = document.getElementById(trigger.dataset.openClubEdit);
        dialog?.showModal();
        dialog?.querySelector('input[name="TenCLB"]')?.focus();
    });
});

document.querySelectorAll('.club-modal').forEach((dialog) => {
    dialog.querySelectorAll('[data-close-club-modal]').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    if (dialog.dataset.openOnLoad === 'true') {
        dialog.showModal();
        dialog.querySelector('input[name="TenCLB"]')?.focus();
    }
});

document.querySelectorAll('.event-form-grid').forEach((formFields) => {
    const typeOptions = [...formFields.querySelectorAll('input[name="LoaiSuKien"]')];
    const internalFields = [...formFields.querySelectorAll('[data-internal-field]')];

    const updateInternalFields = () => {
        const isInternal = typeOptions.find((option) => option.checked)?.value === 'NOI_BO';
        internalFields.forEach((field) => {
            field.hidden = !isInternal;
        });
    };

    typeOptions.forEach((option) => option.addEventListener('change', updateInternalFields));
    updateInternalFields();
});

document.querySelector('[data-fullscreen-toggle]')?.addEventListener('click', async () => {
    if (!document.fullscreenElement) {
        await document.documentElement.requestFullscreen();
    } else {
        await document.exitFullscreen();
    }
});

bulkForm?.addEventListener('submit', (event) => {
    const submitter = event.submitter;

    if (eventCheckboxes.every((checkbox) => !checkbox.checked)) {
        event.preventDefault();
        return;
    }

    if (submitter?.dataset.confirm && !window.confirm(bulkForm.dataset.confirmDelete)) {
        event.preventDefault();
    }
});

updateSelection();
