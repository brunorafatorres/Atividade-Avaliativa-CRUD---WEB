<?php
require_once 'conexao.php';

/*
CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(100) NOT NULL,
    endereco TEXT NOT NULL,
    status ENUM('pendente','entregue') DEFAULT 'pendente'
);
*/

function inserirEntrega($pdo, $destinatario, $endereco) {
    $stmt = $pdo->prepare("INSERT INTO entregas (destinatario, endereco) VALUES (?, ?)");
    return $stmt->execute([$destinatario, $endereco]);
}
function listarEntregas($pdo, $status = null) {
    $sql = "SELECT * FROM entregas";
    if ($status) $sql .= " WHERE status = '$status'";
    $sql .= " ORDER BY destinatario";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
function buscarEntrega($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarEntrega($pdo, $id, $destinatario, $endereco) {
    $stmt = $pdo->prepare("UPDATE entregas SET destinatario=?, endereco=? WHERE id=?");
    return $stmt->execute([$destinatario, $endereco, $id]);
}
function deletarEntrega($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM entregas WHERE id=?");
    return $stmt->execute([$id]);
}
function marcarEntregue($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE entregas SET status = 'entregue' WHERE id = ?");
    return $stmt->execute([$id]);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirEntrega($pdo, $_POST['destinatario'], $_POST['endereco']);
        $mensagem = "Entrega adicionada!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarEntrega($pdo, $_POST['id'], $_POST['destinatario'], $_POST['endereco']);
        $mensagem = "Entrega atualizada!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarEntrega($pdo, $_GET['deletar']);
    $mensagem = "Entrega removida!";
    $action = 'list';
}
if (isset($_GET['entregar'])) {
    marcarEntregue($pdo, $_GET['entregar']);
    $mensagem = "Status atualizado para entregue!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Controle de Entregas</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1> Controle de Entregas</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Nova entrega</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $pendentes = listarEntregas($pdo, 'pendente');
    $entregues = listarEntregas($pdo, 'entregue');
?>
    <h2>Pendentes</h2>
    <?php if(count($pendentes) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Destinatário</th><th>Endereço</th><th>Ações</th></tr>
            <?php foreach($pendentes as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['destinatario']) ?></td>
                <td><?= htmlspecialchars($e['endereco']) ?></td>
                <td>
                    <a href="?entregar=<?= $e['id'] ?>">Marcar entregue</a> |
                    <a href="?action=edit&id=<?= $e['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $e['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma entrega pendente.</p>"; endif; ?>

    <h2>Entregues</h2>
    <?php if(count($entregues) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Destinatário</th><th>Endereço</th><th>Ações</th></tr>
            <?php foreach($entregues as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['destinatario']) ?></td>
                <td><?= htmlspecialchars($e['endereco']) ?></td>
                <td>
                    <a href="?action=edit&id=<?= $e['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $e['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma entrega entregue.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $entrega = $editando ? buscarEntrega($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Entrega' : 'Nova Entrega' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $entrega['id'] ?>"><?php endif; ?>
        Destinatário: <input type="text" name="destinatario" value="<?= $editando ? htmlspecialchars($entrega['destinatario']) : '' ?>" required><br>
        Endereço: <textarea name="endereco" required><?= $editando ? htmlspecialchars($entrega['endereco']) : '' ?></textarea><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>