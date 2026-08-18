/**
 * Adds a Show/Hide button to every password field on the page.
 *
 * Must load AFTER a page's own inline script: the register page inserts
 * validation-error slots as siblings of each input, and if this ran first,
 * those slots would land inside the wrapper created here, throwing off the
 * button's vertical centering.
 */
(function () {
    'use strict';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        // Guard against double-enhancement.
        if (input.dataset.pwToggle) {
            return;
        }
        input.dataset.pwToggle = 'ready';

        var wrap = document.createElement('div');
        wrap.className = 'pw-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var button = document.createElement('button');
        // type="button" — without it, clicking would submit the form.
        button.type = 'button';
        button.className = 'pw-toggle';
        button.textContent = 'Show';
        button.setAttribute('aria-label', 'Show password');
        button.setAttribute('aria-pressed', 'false');
        wrap.appendChild(button);

        button.addEventListener('click', function () {
            var isRevealed = input.type === 'text';

            input.type = isRevealed ? 'password' : 'text';
            button.textContent = isRevealed ? 'Show' : 'Hide';
            button.setAttribute('aria-label', isRevealed ? 'Show password' : 'Hide password');
            button.setAttribute('aria-pressed', String(!isRevealed));

            input.focus();
        });

        // Never leave a password revealed after the form is sent.
        if (input.form) {
            input.form.addEventListener('submit', function () {
                input.type = 'password';
                button.textContent = 'Show';
                button.setAttribute('aria-label', 'Show password');
                button.setAttribute('aria-pressed', 'false');
            });
        }
    });
})();
