<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .reset-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 4px;
            display: none;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Reset Your Password</h2>
        
        <div id="message" class="message"></div>
        
        <form id="resetForm">
            <input type="hidden" id="token" value="{{ request()->query('token') }}">
            <input type="hidden" id="email" value="{{ request()->query('email') }}">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" required>
                <small>Must contain at least 8 characters, 1 uppercase, 1 lowercase, 1 number, and 1 special character</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" required>
            </div>
            
            <button type="submit">Reset Password</button>
        </form>
    </div>

    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const token = document.getElementById('token').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;
            
            const messageEl = document.getElementById('message');
            messageEl.style.display = 'none';
            
            fetch('/api/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token,
                    email,
                    password,
                    password_confirmation
                })
            })
            .then(response => response.json())
            .then(data => {
                messageEl.style.display = 'block';
                if (data.status === 'success') {
                    messageEl.className = 'message success';
                    messageEl.textContent = data.message;
                    document.getElementById('resetForm').reset();
                    
                    // Optional: Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 3000);
                } else {
                    messageEl.className = 'message error';
                    messageEl.textContent = data.message || 'An error occurred';
                }
            })
            .catch(error => {
                messageEl.style.display = 'block';
                messageEl.className = 'message error';
                messageEl.textContent = 'An error occurred while resetting password';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>