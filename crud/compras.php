<?php
require_once 'conexao.php';

/*
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto VARCHAR(100) NOT NULL,
    quantidade INT NOT NULL,
    comprado BOOLEAN DEFAULT 0
);
*/

function inserirCompra($pdo, $produto, $quantidade) {
    $stmt = $pdo->prepare("INSERT INTO compras (produto, quantidade) VALUES (?, ?)");
    return $stmt->execute([$produto, $quantidade]);
}
function listarCompras($pdo) {
    return $pdo->query("SELECT * FROM compras ORDER BY produto")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarCompra($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM compras WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarCompra($pdo, $id, $produto, $quantidade) {
    $stmt = $pdo->prepare("UPDATE compras SET produto=?, quantidade=? WHERE id=?");
    return $stmt->execute([$produto, $quantidade, $id]);
}
function deletarCompra($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM compras WHERE id=?");
    return $stmt->execute([$id]);
}
function marcarComprado($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE compras SET comprado = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}
function totalPendentes($pdo) {
    return $pdo->query("SELECT COUNT(*) FROM compras WHERE comprado = 0")->fetchColumn();
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirCompra($pdo, $_POST['produto'], $_POST['quantidade']);
        $mensagem = "Item adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarCompra($pdo, $_POST['id'], $_POST['produto'], $_POST['quantidade']);
        $mensagem = "Item atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarCompra($pdo, $_GET['deletar']);
    $mensagem = "Item removido!";
    $action = 'list';
}
if (isset($_GET['comprar'])) {
    marcarComprado($pdo, $_GET['comprar']);
    $mensagem = "Item marcado como comprado!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Lista de Compras</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Lista de Compras Inteligente</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add"> + Novo item</a>
<?php endif; ?>
<p><strong>Itens pendentes: <?= totalPendentes($pdo) ?></strong></p>

<?php if($action === 'list'): 
    $compras = listarCompras($pdo);
    if(count($compras) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Produto</th><th>Quantidade</th><th>Status</th><th>Ações</th></tr>
            <?php foreach($compras as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['produto']) ?></td>
                <td><?= $c['quantidade'] ?></td>
                <td><?= $c['comprado'] ? 'Comprado' : 'Pendente' ?></td>
                <td>
                    <?php if(!$c['comprado']): ?>
                        <a href="?comprar=<?= $c['id'] ?>">Marcar comprado</a> |
                    <?php endif; ?>
                    <a href="?action=edit&id=<?= $c['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $c['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum item na lista.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $compra = $editando ? buscarCompra($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Item' : 'Novo Item' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $compra['id'] ?>"><?php endif; ?>
        Produto: <input type="text" name="produto" value="<?= $editando ? htmlspecialchars($compra['produto']) : '' ?>" required><br>
        Quantidade: <input type="number" name="quantidade" value="<?= $editando ? $compra['quantidade'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>