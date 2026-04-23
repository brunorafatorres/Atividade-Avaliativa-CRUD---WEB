<?php
require_once 'conexao.php';

// SQL PARA CRIAR A TABELA:
/*
CREATE TABLE pratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(50) NOT NULL
);
*/

function inserirPrato($pdo, $nome, $descricao, $preco, $categoria) {
    $sql = "INSERT INTO pratos (nome, descricao, preco, categoria) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nome, $descricao, $preco, $categoria]);
}

function listarPratos($pdo) {
    $sql = "SELECT * FROM pratos ORDER BY categoria, nome";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function buscarPrato($pdo, $id) {
    $sql = "SELECT * FROM pratos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function atualizarPrato($pdo, $id, $nome, $descricao, $preco, $categoria) {
    $sql = "UPDATE pratos SET nome=?, descricao=?, preco=?, categoria=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nome, $descricao, $preco, $categoria, $id]);
}

function deletarPrato($pdo, $id) {
    $sql = "DELETE FROM pratos WHERE id=?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$id]);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        if (inserirPrato($pdo, $_POST['nome'], $_POST['descricao'], $_POST['preco'], $_POST['categoria']))
            $mensagem = "Prato cadastrado!";
        else $mensagem = "Erro ao cadastrar.";
        $action = 'list';
    }
    elseif (isset($_POST['editar'])) {
        if (atualizarPrato($pdo, $_POST['id'], $_POST['nome'], $_POST['descricao'], $_POST['preco'], $_POST['categoria']))
            $mensagem = "Prato atualizado!";
        else $mensagem = "Erro na atualização.";
        $action = 'list';
    }
}

if (isset($_GET['deletar'])) {
    deletarPrato($pdo, $_GET['deletar']);
    $mensagem = "Prato removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Cardápio Digital</title><style>body{font-family:Arial;margin:20px} table{border-collapse:collapse} th,td{padding:8px}</style></head>
<body>
<h1>🍽️ Cardápio Digital</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">➕ Novo Prato</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $pratos = listarPratos($pdo);
    $categorias = [];
    foreach ($pratos as $p) $categorias[$p['categoria']][] = $p;
    if(count($pratos) == 0): ?>
        <p>Nenhum prato cadastrado.</p>
    <?php else: 
        foreach ($categorias as $cat => $itens): ?>
            <h2><?= htmlspecialchars($cat) ?></h2>
            <table border="1">
                <tr><th>Nome</th><th>Descrição</th><th>Preço</th><th>Ações</th></tr>
                <?php foreach ($itens as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['descricao']) ?></td>
                    <td>R$ <?= number_format($p['preco'],2,',','.') ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $p['id'] ?>">Editar</a> |
                        <a href="?deletar=<?= $p['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; 
    endif;

elseif($action === 'add' || $action === 'edit'): 
    $editando = ($action === 'edit');
    $prato = $editando ? buscarPrato($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Prato' : 'Novo Prato' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $prato['id'] ?>"><?php endif; ?>
        Nome: <input type="text" name="nome" value="<?= $editando ? htmlspecialchars($prato['nome']) : '' ?>" required><br>
        Descrição: <textarea name="descricao"><?= $editando ? htmlspecialchars($prato['descricao']) : '' ?></textarea><br>
        Preço: <input type="number" step="0.01" name="preco" value="<?= $editando ? $prato['preco'] : '' ?>" required><br>
        Categoria: <input type="text" name="categoria" value="<?= $editando ? htmlspecialchars($prato['categoria']) : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>