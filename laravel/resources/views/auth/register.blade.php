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
            <h1>Create Account</h1>
            <p class="auth-subtitle">Register as a student looking for internships, or an employer looking to hire.</p>

            <div id="alert-box" class="alert"></div>

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
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" maxlength="20">
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required maxlength="100">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required minlength="8">
                            <p class="field-hint">Minimum 8 characters.</p>
                        </div>
                        <div class="field-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
                        </div>
                    </div>
                </fieldset>

                <!-- Student-only fields -->
                <fieldset class="role-fields active" data-role-fields="Student" id="fields-student">
                    <legend>Academic Details</legend>

                    <div class="field-group">
                        <label for="matric_no">Matric Number</label>
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
                        <input type="number" id="graduation_year" name="graduation_year" min="1900" max="2100">
                    </div>
                </fieldset>

                <!-- Employer-only fields -->
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
                        <label for="company_name">Company Name</label>
                        <input type="text" id="company_name" name="company_name" maxlength="150">
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="company_email">Company Email</label>
                            <input type="email" id="company_email" name="company_email" maxlength="100">
                        </div>
                        <div class="field-group">
                            <label for="company_phone">Company Phone</label>
                            <input type="tel" id="company_phone" name="company_phone" maxlength="20">
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field-group">
                            <label for="industry">Industry</label>
                            <input type="text" id="industry" name="industry" maxlength="100">
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

            <p class="auth-footer-link">Already have an account? <a href="{{ url('/login') }}">Log in</a></p>
        </div>
    </main>

    <script>
        // --- Role tab switching ---
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

        // --- Google Maps: Places Autocomplete + marker, fills city/state/postcode ---
        let map, marker;

        function initAutocomplete() {
            const mapDiv = document.getElementById('map-picker');
            mapDiv.textContent = '';

            const center = { lat: 3.139, lng: 101.6869 }; // Kuala Lumpur, adjust as needed
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

        // --- Form submission ---
        const form = document.getElementById('register-form');
        const alertBox = document.getElementById('alert-box');
        const submitBtn = document.getElementById('submit-btn');

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = 'alert show alert-' + type;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            alertBox.className = 'alert';

            if (document.getElementById('password').value !== document.getElementById('password_confirmation').value) {
                showAlert('Passwords do not match.', 'error');
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
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : data.message;
                    showAlert(firstError || 'Registration failed.', 'error');
                    return;
                }

                showAlert(data.message + ' Redirecting to login...', 'success');
                setTimeout(() => { window.location.href = '{{ url('/login') }}'; }, 1800);
            } catch (err) {
                showAlert('Network error. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            }
        });
    </script>
</body>
</html>
