/**
 * Adds a Show/Hide button to every password field on the page.
 *
 * Self-contained on purpose: it finds the fields itself rather than needing
 * markup changes, so any password input added later is picked up for free.
 *
 * Load this AFTER a page's own inline script. The register page inserts its
 * validation-error slots as siblings of each input; if this ran first, those
 * slots would land inside the wrapper created here and throw off the button's
 * vertical centering.
 */
(function () {
    'use strict';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        // Guard against double-enhancement if this script is ever included twice.
        if (input.dataset.pwToggle) {
            return;
        }
        input.dataset.pwToggle = 'ready';

        var wrap = document.createElement('div');
        wrap.className = 'pw-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var button = document.createElement('button');
        // Explicitly type="button" — without it this would submit the form.
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

            // Put the caret back so the user can keep typing where they left off.
            input.focus();
        });

        // Never leave a password on screen after the form is sent.
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
