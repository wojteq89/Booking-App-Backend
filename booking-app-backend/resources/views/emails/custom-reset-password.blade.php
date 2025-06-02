<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      padding: 20px;
      margin: 0;
    }

    .container {
        text-align: center;
    }

    h2 {
      font-size: 30px;
      color: #414e66; /* $primary */
      margin: 0;
    }

    p {
      font-size: 20px;
      color: #414e66; /* $primary */
      margin: 0 0 10px 0;
    }

    .custom-button {
      background-color: transparent;
      color: #414e66; /* $primary */
      border: 2px solid #414e66; /* $primary */
      padding: 12px 20px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 20px;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      transition: all 0.5s ease-in-out;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      width: max-content;
    }

    .custom-button:hover {
      background-color: #414e66; /* $primary */
      color: #fff; /* $white */
      border: 2px solid transparent;
    }
  </style>
</head>
<body>
    <div class="container">
        <img src="https://raw.githubusercontent.com/wojteq89/Booking-App-Frontend/refs/heads/dev/Booking-App-Frontend/src/assets/Graphics/logo_cale_v3.png" alt="Logo" width="150" />
        <h2>Resetowanie hasła</h2>
        <p>Cześć {{ $user->first_name ?? 'Użytkowniku' }},</p>
        <p>Otrzymaliśmy prośbę o zresetowanie Twojego hasła. Kliknij przycisk poniżej, aby ustawić nowe hasło:</p>
        <a href="{{ $url }}" class="custom-button">Zresetuj hasło</a>
        <p>Jeśli to nie Ty inicjowałeś reset, zignoruj tę wiadomość.</p>
    </div>
</body>
</html>
