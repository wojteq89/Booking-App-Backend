<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <title>Resetuj hasło</title>
  <style>

    * {
      box-sizing: border-box;
      transition: all 0.5s ease-in-out;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background-color: #fff; /* $white */
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 0 10px 2px rgba(0, 0, 0, 0.6); /* $shadow */
      width: 80vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: #414e66; /* $primary */
    }

    h2.title {
      font-size: 30px;
      color: #414e66; /* $primary */
    }

    form.custom-form {
      display: flex;
      flex-direction: column;
      gap: 30px;
      width: 100%;
      overflow: hidden;
    }

    .row {
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    label {
      font-size: 16px;
      color: #414e66; /* $primary */
      font-weight: 600;
    }

    input.input-field {
      font-size: large;
      padding: 10px;
      border: 2px solid transparent;
      border-bottom: 2px solid #414e66; /* $primary */
      background-color: #fff; /* $white */
      color: #414e66; /* $primary */
      border-radius: 0;
      transition: all 0.5s ease-in-out;
      font-family: 'Poppins', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 100%;
    }

    input.input-field:focus {
      outline: none;
      border: 2px solid #414e66; /* $primary */
      border-radius: 10px;
    }

    input.input-field:hover {
      border: 2px solid #414e66; /* $primary */
      border-radius: 10px;
    }

    button.custom-button {
      background-color: transparent;
      color: #414e66; /* $primary */
      border: 2px solid #414e66; /* $primary */
      padding: 10px 20px;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.5s ease-in-out;
      font-family: 'Poppins', sans-serif;
      width: max-content;
      align-self: center;
      margin-bottom: 20px;
    }

    button.custom-button:hover {
      background-color: #414e66; /* $primary */
      color: #fff; /* $white */
      border: 2px solid transparent;
    }

    .status-message {
      color: green;
      font-weight: 600;
    }

    .error-messages {
      color: red; /* $red */
      font-weight: 600;
    }

    .error-messages ul {
      padding-left: 20px;
      margin: 0;
    }

    @media screen and (max-width: 900px) {
      .container {
        width: 90vw;
        padding: 20px;
        box-shadow: none;
        
      }

      h2.title {
        font-size: 24px;
      }

      input.input-field, button.custom-button {
        font-size: 16px;
      }
      
    }

  </style>
</head>
<body>
  <div class="container">
    <img src="https://raw.githubusercontent.com/wojteq89/Booking-App-Frontend/refs/heads/dev/Booking-App-Frontend/src/assets/Graphics/logo_cale_v3.png" alt="Logo" width="150" />
    <h2 class="title">Resetuj hasło</h2>

    @if(session('status'))
      <p class="status-message">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ url('/api/reset-password') }}" class="custom-form">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">

      <div class="row">
        <label for="email">Email:</label>
        <input id="email" type="email" name="email" required class="input-field" />
      </div>

      <div class="row">
        <label for="password">Nowe hasło:</label>
        <input id="password" type="password" name="password" required class="input-field" />
      </div>

      <div class="row">
        <label for="password_confirmation">Powtórz hasło:</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required class="input-field" />
      </div>

      <button type="submit" class="custom-button">Zmień hasło</button>
    </form>

    @if ($errors->any())
      <div class="error-messages">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>
</body>
</html>
