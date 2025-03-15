<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Form</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h2>Select Options</h2>

    <form id="toggleForm" data-route="{{ route('toggle.submit') }}">
        @csrf  {{-- CSRF token for security --}}

        <label>
            <input type="checkbox" name="email" value="1"> Email
        </label>
        <br>
        <label>
            <input type="checkbox" name="phone" value="1"> Phone Number
        </label>
        <br>

        <h3>Custom Fields</h3>
        <div id="custom-fields"></div>
        <button type="button" id="addCustomField">Add Custom Field</button>
        <br><br>

        <button type="submit">Submit</button>
    </form>

    <script>
        $(document).ready(function () {
            let customFieldIndex = 0;

            // Add custom field
            $('#addCustomField').click(function () {
                $('#custom-fields').append(`
                    <div class="custom-field">
                        <input type="text" name="custom[${customFieldIndex}][value]" placeholder="Enter value">
                        <button type="button" class="remove-field">Remove</button>
                    </div>
                `);
                customFieldIndex++;
            });

            // Remove custom field
            $(document).on('click', '.remove-field', function () {
                $(this).parent('.custom-field').remove();
            });

            // Handle form submission
            $('#toggleForm').submit(function (event) {
                event.preventDefault();

                let formData = {
                    email: $('input[name="email"]').prop('checked'),
                    phone: $('input[name="phone"]').prop('checked'),
                    custom: []
                };

                // Get all custom fields
                $('.custom-field input[type="text"]').each(function () {
                    let value = $(this).val();
                    if (value.trim() !== '') {
                        formData.custom.push({ value });
                    }
                });

                let formUrl = $('#toggleForm').data('route');

                // Send data via AJAX
                $.ajax({
                    url: formUrl,
                    type: "POST",
                    data: JSON.stringify(formData),
                    contentType: "application/json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        alert("Form submitted successfully!");
                        console.log(response);
                    },
                    error: function (xhr, status, error) {
                        console.error("Error:", xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html>
