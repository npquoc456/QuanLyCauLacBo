document.querySelectorAll('[data-auth-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const message = document.querySelector('[data-auth-message]');
        const submitButton = form.querySelector('[type="submit"]');
        const submitLabel = form.querySelector('[data-submit-label]');
        const originalLabel = submitLabel.textContent;
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        payload.remember = formData.has('remember');
        delete payload.terms;

        message.hidden = true;
        message.textContent = '';
        submitButton.disabled = true;
        submitLabel.textContent = 'Đang xử lý...';

        try {
            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();

            if (!response.ok) {
                const firstError = Object.values(result.errors ?? {})[0]?.[0];
                throw new Error(firstError ?? result.message ?? 'Không thể xử lý yêu cầu. Vui lòng thử lại.');
            }

            if (form.dataset.formType === 'register') {
                const loginUrl = new URL(result.redirect, window.location.origin);
                loginUrl.searchParams.set('registered', '1');
                loginUrl.searchParams.set('identifier', payload.TenTaiKhoan);
                window.location.assign(loginUrl);

                return;
            }

            window.location.assign(result.redirect);
        } catch (error) {
            message.textContent = error.message;
            message.hidden = false;
        } finally {
            submitButton.disabled = false;
            submitLabel.textContent = originalLabel;
        }
    });
});

document.querySelectorAll('.auth-password-toggle').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const showingPassword = input.type === 'password';
        input.type = showingPassword ? 'text' : 'password';
        button.setAttribute('aria-label', showingPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
});
