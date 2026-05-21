<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário';
$homeUrl = $_SESSION['homeUrl'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #343a40; /* Darker gray for a modern look */
            border-radius: 10px;
            margin: 10px auto; /* Center navbar and add spacing */
            padding: 0.5rem 1rem;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #f8f9fa;
            padding-left: 10px;
        }

        .navbar-nav .nav-link {
            font-size: 1rem;
            color: #eaeaea;
            transition: color 0.3s ease, background-color 0.3s ease;
            border-radius: 5px;
            margin: 0 5px;
        }

        .navbar-nav .nav-link:hover {
            background-color: #495057;
            color: #f8f9fa;
        }

        .nav-link.active {
            background-color: #007bff;
            color: #fff !important;
        }

        .dropdown-menu {
            border-radius: 10px;
            background-color: #343a40;
        }

        .dropdown-menu .dropdown-item {
            color: #eaeaea;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-menu .dropdown-item:hover {
            background-color: #495057;
            color: #fff;
        }

        .dropdown-menu-end {
            text-align: left;
        }

        .navbar-toggler {
            border-color: #f8f9fa;
        }

        .navbar-toggler-icon {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema de Estoque</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active back-button" href="#">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cadastro_estoque.php">Cadastro de Estoque</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="consulta_deposito.php">Consulta de Estoque</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="emissao_notas.php">Emissão de Notas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorionf.php">Relatório De Nota Fiscal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="painel-adm.php">Administrador</a>
                    </li>
                    <li>
                                <a class="dropdown-item" href="../back/usuariocontroller.php?action=logout">Sair</a>
                            </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($usuarioNome, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="usuarioDropdown">
                            <li class="dropdown-item-text">
                                <a href="painel-adm.php"><?php echo htmlspecialchars($usuarioNome, ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="../back/usuariocontroller.php?action=logout">Sair</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let homeUrl = '<?php echo htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8'); ?>';
    </script>
    <script src="../assets/js/voltar_home.js"></script>
</body>

</html>
