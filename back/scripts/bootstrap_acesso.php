<?php

require_once __DIR__ . '/../db.php';

function logLine(string $message): void
{
    echo $message . PHP_EOL;
}

try {
    $conn = getConnection();

    if (!$conn) {
        throw new RuntimeException('Falha ao conectar ao banco.');
    }

    $conn->exec("
        CREATE TABLE IF NOT EXISTS funcoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id_usuario INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL,
            sobrenome VARCHAR(120) NOT NULL,
            funcao_id INT NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            CONSTRAINT fk_usuarios_funcoes
                FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
                ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS paginas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $conn->exec("
        CREATE TABLE IF NOT EXISTS permissoes_paginas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pagina_id INT NOT NULL,
            funcao_id INT NOT NULL,
            UNIQUE KEY uk_pagina_funcao (pagina_id, funcao_id),
            CONSTRAINT fk_permissoes_paginas
                FOREIGN KEY (pagina_id) REFERENCES paginas(id)
                ON UPDATE CASCADE ON DELETE CASCADE,
            CONSTRAINT fk_permissoes_funcoes
                FOREIGN KEY (funcao_id) REFERENCES funcoes(id)
                ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $conn->exec("
        INSERT INTO funcoes (id, nome, descricao) VALUES
            (1, 'Venda', 'Função de vendas'),
            (2, 'Administrador', 'Função administrativa'),
            (3, 'Estoque', 'Função de estoque'),
            (4, 'Supervisor', 'Função de supervisão')
        ON DUPLICATE KEY UPDATE
            nome = VALUES(nome),
            descricao = VALUES(descricao);
    ");

    $conn->exec("ALTER TABLE funcoes AUTO_INCREMENT = 5;");

    $jsonPath = __DIR__ . '/../data/permissoes.json';
    if (!file_exists($jsonPath)) {
        throw new RuntimeException("Arquivo de permissões não encontrado: {$jsonPath}");
    }

    $jsonRaw = file_get_contents($jsonPath);
    $json = json_decode($jsonRaw, true);

    if (!is_array($json) || !isset($json['paginas']) || !is_array($json['paginas'])) {
        throw new RuntimeException('Estrutura inválida no arquivo permissoes.json');
    }

    $stmtInsertPagina = $conn->prepare("INSERT IGNORE INTO paginas (nome) VALUES (:nome)");
    $stmtFindPagina = $conn->prepare("SELECT id FROM paginas WHERE nome = :nome LIMIT 1");
    $stmtFindFuncao = $conn->prepare("SELECT id FROM funcoes WHERE nome = :nome LIMIT 1");
    $stmtInsertPermissao = $conn->prepare("
        INSERT IGNORE INTO permissoes_paginas (pagina_id, funcao_id)
        VALUES (:pagina_id, :funcao_id)
    ");

    $totalPermissoes = 0;

    foreach ($json['paginas'] as $item) {
        $paginaNome = $item['nome'] ?? null;
        $funcoes = $item['funcoes'] ?? [];

        if (!$paginaNome || !is_array($funcoes)) {
            continue;
        }

        $stmtInsertPagina->execute([':nome' => $paginaNome]);
        $stmtFindPagina->execute([':nome' => $paginaNome]);
        $paginaId = $stmtFindPagina->fetchColumn();

        if (!$paginaId) {
            continue;
        }

        foreach ($funcoes as $funcaoNome) {
            $stmtFindFuncao->execute([':nome' => $funcaoNome]);
            $funcaoId = $stmtFindFuncao->fetchColumn();

            if (!$funcaoId) {
                continue;
            }

            $stmtInsertPermissao->execute([
                ':pagina_id' => (int)$paginaId,
                ':funcao_id' => (int)$funcaoId,
            ]);

            $totalPermissoes += $stmtInsertPermissao->rowCount();
        }
    }

    $totalFuncoes = (int)$conn->query("SELECT COUNT(*) FROM funcoes")->fetchColumn();
    $totalPaginas = (int)$conn->query("SELECT COUNT(*) FROM paginas")->fetchColumn();
    $totalPermissoesAtuais = (int)$conn->query("SELECT COUNT(*) FROM permissoes_paginas")->fetchColumn();

    logLine("Bootstrap concluído com sucesso.");
    logLine("Funções: {$totalFuncoes}");
    logLine("Páginas: {$totalPaginas}");
    logLine("Permissões: {$totalPermissoesAtuais} (novas nesta execução: {$totalPermissoes})");
} catch (Throwable $e) {
    fwrite(STDERR, 'Erro no bootstrap: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

