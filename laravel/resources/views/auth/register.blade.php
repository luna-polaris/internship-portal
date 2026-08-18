<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — InternHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('stylesheet/stylePublic.css') }}">
    <link rel="stylesheet" href="{{ asset('stylesheet/styleAuth.css') }}">
</head>
<body class="auth-body">

    <header class="auth-header">
        <a href="{{ url('/') }}" class="logo">Intern<span>—</span>Hub</a>
        <a href="{{ url('/') }}" class="back-home">&larr; Back to Home</a>
    </header>

    <main class="auth-main">
        <div class="auth-card wide">
            <h1 id="register-heading">Create Account</h1>
            <p class="auth-subtitle" id="register-intro">
                Register as a student looking for internships, or an employer looking to hire.<br>
                Fields marked <span class="req">*</span> are required.
            </p>

            <div id="alert-box" class="alert"></div>

            <!-- Account is status=Pending until the emailed link is clicked, so this must be unmissable, not a flash message. -->
            <div id="register-success" class="verify-panel" role="status" aria-live="polite">
                <div class="verify-panel__icon" aria-hidden="true">&#10003;</div>
                <h2 class="verify-panel__title">Account created</h2>
                <p class="verify-panel__lead">
                    We&rsquo;ve sent a verification link to
                    <strong id="verify-email-address"></strong>
                </p>
                <hr class="verify-panel__divider">
                <p class="verify-panel__text">
                    <strong>You must click that link before you can log in.</strong>
                    Your account stays inactive until the email address is verified.
                </p>
                <p class="verify-panel__text verify-panel__muted">
                    The link expires in 24 hours. If it isn&rsquo;t in your inbox,
                    check your spam or junk folder.
                </p>
                <a href="{{ url('/login') }}" class="btn-submit verify-panel__cta">Go to Login</a>
            </div>

            <div class="role-switch" role="tablist" aria-label="Register as">
                <button type="button" id="tab-student" aria-selected="true" data-role="Student">Student</button>
                <button type="button" id="tab-employer" aria-selected="false" data-role="Employer">Employer</button>
            </div>

            <form id="register-form" novalidate>
                <input type="hidden" name="role" id="role" value="Student">

                <fieldset>
                    <legend>Account Details</legend>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="full_name">Full Name <span class="req">*</span></label>
                            <input type="text" id="full_name" name="full_name" required maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" maxlength="11">
                            <p class="field-hint">Starts with 01, 10&ndash;11 digits total (e.g. 0123456789).</p>
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="email">Email <span class="req">*</span></label>
                        <input type="email" id="email" name="email" required maxlength="100">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="password">Password <span class="req">*</span></label>
                            <input type="password" id="password" name="password" required minlength="12">
                            <ul class="pw-checklist" id="pw-checklist" aria-live="polite">
                                <li data-rule="length">At least 12 characters</li>
                                <li data-rule="upper">An uppercase letter</li>
                                <li data-rule="lower">A lowercase letter</li>
                                <li data-rule="number">A number</li>
                                <li data-rule="special">A special character (e.g. ! @ # $ %)</li>
                            </ul>
                        </div>
                        <div class="field-group">
                            <label for="password_confirmation">Confirm Password <span class="req">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="12">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="role-fields active" data-role-fields="Student" id="fields-student">
                    <legend>Academic Details</legend>

                    <div class="field-group">
                        <label for="matric_no">Matric Number <span class="req">*</span></label>
                        <input type="text" id="matric_no" name="matric_no" maxlength="20">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="university">University</label>
                            <input type="text" id="university" name="university" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="faculty">Faculty</label>
                            <input type="text" id="faculty" name="faculty" maxlength="100">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="programme">Programme</label>
                            <input type="text" id="programme" name="programme" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="cgpa">CGPA</label>
                            <input type="number" id="cgpa" name="cgpa" min="0" max="4" step="0.01">
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="graduation_year">Graduation Year</label>
                        <input type="number" id="graduation_year" name="graduation_year" min="2000" max="2050">
                        <p class="field-hint">Between 2000 and 2050.</p>
                    </div>
                </fieldset>

                <fieldset class="role-fields" data-role-fields="Employer" id="fields-employer">
                    <legend>Job Details</legend>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="position">Your Position</label>
                            <input type="text" id="position" name="position" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department" maxlength="100">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="role-fields" data-role-fields="Employer" id="fields-company">
                    <legend>Company Details</legend>

                    <div class="field-group">
                        <label for="company_name">Company Name <span class="req">*</span></label>
                        <input type="text" id="company_name" name="company_name" maxlength="150">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="company_email">Company Email</label>
                            <input type="email" id="company_email" name="company_email" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="company_phone">Company Phone</label>
                            <input type="tel" id="company_phone" name="company_phone" maxlength="11">
                            <p class="field-hint">Starts with 01, 10&ndash;11 digits total.</p>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="industry">Industry</label>
                            <select id="industry" name="industry">
                                <option value="">Select an industry</option>
                                <option value="Finance">Finance & Accounting</option>
                                <option value="Biotechnology">Biotechnology</option>
                                <option value="IT">Information Technology</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Healthcare">Healthcare</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" maxlength="150">
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="description">Company Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>

                    <div class="field-group">
                        <label for="address_search">Company Address <span style="text-transform:none;color:var(--steel)">(search to auto-fill city / state / postcode)</span></label>
                        <input type="text" id="address_search" name="address" autocomplete="off" placeholder="Start typing an address...">
                        <div id="map-picker">
                            @if (config('services.google.maps_key'))
                                Loading map…
                            @else
                                Google Maps is not configured (GOOGLE_MAPS_API_KEY is empty in .env). Fill in city / state / postcode manually below.
                            @endif
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" maxlength="100">
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="postcode">Postcode</label>
                        <input type="text" id="postcode" name="postcode" maxlength="10">
                    </div>
                </fieldset>

                <button type="submit" class="btn-submit" id="submit-btn">Create Account</button>
            </form>

            <p class="auth-footer-link" id="register-footer-link">Already have an account? <a href="{{ url('/login') }}">Log in</a></p>
        </div>
    </main>

    <script>
        const roleInput = document.getElementById('role');
        const tabs = document.querySelectorAll('.role-switch button');

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((t) => t.setAttribute('aria-selected', 'false'));
                tab.setAttribute('aria-selected', 'true');
                const role = tab.dataset.role;
                roleInput.value = role;

                document.querySelectorAll('[data-role-fields]').forEach((section) => {
                    section.classList.toggle('active', section.dataset.roleFields === role);
                });

                // Required attributes should only apply to the visible role's fields.
                document.getElementById('matric_no').required = role === 'Student';
                document.getElementById('company_name').required = role === 'Employer';
            });
        });
        document.getElementById('matric_no').required = true;

        let map, marker;

        function initAutocomplete() {
            const mapDiv = document.getElementById('map-picker');
            mapDiv.textContent = '';

            const center = { lat: 3.139, lng: 101.6869 }; // Kuala Lumpur placeholder, adjust as needed
            map = new google.maps.Map(mapDiv, { center, zoom: 11 });
            marker = new google.maps.Marker({ map, position: center });

            const input = document.getElementById('address_search');
            const autocomplete = new google.maps.places.Autocomplete(input, {
                fields: ['address_components', 'formatted_address', 'geometry'],
            });
            autocomplete.bindTo('bounds', map);

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }

                map.setCenter(place.geometry.location);
                map.setZoom(15);
                marker.setPosition(place.geometry.location);

                input.value = place.formatted_address || input.value;

                let city = '', state = '', postcode = '';
                (place.address_components || []).forEach((component) => {
                    const types = component.types;
                    if (types.includes('locality') || types.includes('administrative_area_level_2')) {
                        city = city || component.long_name;
                    }
                    if (types.includes('administrative_area_level_1')) {
                        state = component.long_name;
                    }
                    if (types.includes('postal_code')) {
                        postcode = component.long_name;
                    }
                });

                document.getElementById('city').value = city;
                document.getElementById('state').value = state;
                document.getElementById('postcode').value = postcode;
            });
        }

        @if (config('services.google.maps_key'))
            (function () {
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places&callback=initAutocomplete';
                script.async = true;
                document.head.appendChild(script);
            })();
        @endif

        // Mirrors Laravel's Password rule (min 12, mixedCase, numbers, symbols) so the ticks never disagree with the server.
        const passwordRules = {
            length: (value) => value.length >= 12,
            upper: (value) => /\p{Lu}/u.test(value),
            lower: (value) => /\p{Ll}/u.test(value),
            number: (value) => /\p{N}/u.test(value),
            special: (value) => /\p{Z}|\p{S}|\p{P}/u.test(value),
        };

        const passwordInput = document.getElementById('password');
        const checklistItems = document.querySelectorAll('#pw-checklist li');

        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            checklistItems.forEach((item) => {
                const met = passwordRules[item.dataset.rule](value);
                item.classList.toggle('met', met);
            });
        });

        const form = document.getElementById('register-form');
        const alertBox = document.getElementById('alert-box');
        const submitBtn = document.getElementById('submit-btn');

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'alert show alert-' + type;
        }

        // Each field gets its own error slot above the input, rather than errors bunched at the top of the form.
        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach((control) => {
            if (!control.name) return;
            const slot = document.createElement('p');
            slot.className = 'field-error';
            slot.dataset.errorFor = control.name;
            control.parentNode.insertBefore(slot, control);
        });

        function clearFieldErrors() {
            form.querySelectorAll('.field-error').forEach((slot) => {
                slot.textContent = '';
                slot.classList.remove('show');
            });
            form.querySelectorAll('.is-invalid').forEach((control) => {
                control.classList.remove('is-invalid');
            });
        }

        // errors: { field_name: ["message", ...], ... } — Laravel's 422 shape. Fields with no matching slot fall back to the top alert.
        function showFieldErrors(errors) {
            let firstInvalid = null;
            const orphaned = [];

            Object.entries(errors).forEach(([field, messages]) => {
                const list = Array.isArray(messages) ? messages : [messages];
                const slot = form.querySelector(`.field-error[data-error-for="${field}"]`);
                const control = form.querySelector(`[name="${field}"]`);

                if (!slot) {
                    orphaned.push(list[0]);
                    return;
                }

                // A field can fail several rules at once, so show all of them rather than one error per attempt.
                slot.textContent = '';
                list.forEach((message, index) => {
                    if (index > 0) slot.appendChild(document.createElement('br'));
                    slot.appendChild(document.createTextNode(message));
                });
                slot.classList.add('show');

                if (control) {
                    control.classList.add('is-invalid');
                    if (!firstInvalid) firstInvalid = control;
                }
            });

            if (orphaned.length) {
                showAlert(orphaned[0], 'error');
            }

            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus({ preventScroll: true });
            }
        }

        form.addEventListener('input', (event) => {
            const control = event.target;
            if (!control.name || !control.classList.contains('is-invalid')) return;

            control.classList.remove('is-invalid');
            const slot = form.querySelector(`.field-error[data-error-for="${control.name}"]`);
            if (slot) {
                slot.textContent = '';
                slot.classList.remove('show');
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            alertBox.className = 'alert';
            clearFieldErrors();

            // Quick local check to avoid a round-trip for a typo; the server also enforces this via the `confirmed` rule.
            if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                showFieldErrors({ password_confirmation: ['Password confirmation does not match.'] });
                return;
            }

            const formData = new FormData(form);
            const payload = {};
            formData.forEach((value, key) => {
                if (value !== '') payload[key] = value;
            });

            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';

            try {
                const response = await fetch('{{ url('/api/register') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        showFieldErrors(data.errors);
                    } else {
                        showAlert(data.message || 'Registration failed.', 'error');
                    }
                    return;
                }

                // Show the "verify your email" panel instead of redirecting to /login, so the user sees the instruction they must act on.
                document.getElementById('verify-email-address').textContent = data.masked_email;
                document.getElementById('register-heading').textContent = 'Check Your Email';
                document.getElementById('register-intro').style.display = 'none';
                document.getElementById('register-footer-link').style.display = 'none';
                document.querySelector('.role-switch').style.display = 'none';
                form.style.display = 'none';
                alertBox.className = 'alert';
                document.getElementById('register-success').classList.add('show');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        });
    </script>

    <script src="{{ asset('scripts/passwordToggle.js') }}"></script>
</body>
</html>
