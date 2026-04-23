<?php
require_once 'conexao.php';

/*
CREATE TABLE feedbacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL,
    nota INT CHECK (nota BETWEEN 1 AND 5)
);
*/

function inserirFeedback($pdo, $mensagem, $nota) {
    $stmt = $pdo->prepare("INSERT INTO feedbacks (mensagem, nota) VALUES (?, ?)");
    return $stmt->execute([$mensagem, $nota]);
}
function listarFeedbacks($pdo) {
    return $pdo->query("SELECT * FROM feedbacks ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarFeedback($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM feedbacks WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarFeedback($pdo, $id, $mensagem, $nota) {
    $stmt = $pdo->prepare("UPDATE feedbacks SET mensagem=?, nota=? WHERE id=?");
    return $stmt->execute([$mensagem, $nota, $id]);
}
function deletarFeedback($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id=?");
    return $stmt->execute([$id]);
}
function mediaNotas($pdo) {
    $res = $pdo->query("SELECT AVG(nota) as media FROM feedbacks")->fetch();
    return round($res['media'] ?? 0, 2);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirFeedback($pdo, $_POST['mensagem'], $_POST['nota']);
        $mensagem = "Feedback enviado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarFeedback($pdo, $_POST['id'], $_POST['mensagem'], $_POST['nota']);
        $mensagem = "Feedback atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarFeedback($pdo, $_GET['deletar']);
    $mensagem = "Feedback removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Feedback Anônimo</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1> Feedback Anônimo</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add"> Novo feedback</a>
<?php endif; ?>
<p><strong>Média das notas: <?= mediaNotas($pdo) ?></strong></p>

<?php if($action === 'list'): 
    $feedbacks = listarFeedbacks($pdo);
    if(count($feedbacks) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Mensagem</th><th>Nota</th><th>Ações</th></tr>
            <?php foreach($feedbacks as $f): ?>
            <tr>
                <td><?= nl2br(htmlspecialchars($f['mensagem'])) ?></td>
                <td><?= str_repeat('⭐', $f['nota']) ?> (<?= $f['nota'] ?>)</td>
                <td>
                    <a href="?action=edit&id=<?= $f['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $f['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum feedback cadastrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $feedback = $editando ? buscarFeedback($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Feedback' : 'Novo Feedback' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $feedback['id'] ?>"><?php endif; ?>
        Mensagem: <textarea name="mensagem" required><?= $editando ? htmlspecialchars($feedback['mensagem']) : '' ?></textarea><br>
        Nota (1 a 5): <select name="nota">
            <?php for($i=1;$i<=5;$i++): ?>
                <option value="<?= $i ?>" <?= $editando && $feedback['nota']==$i ? 'selected' : '' ?>><?= $i ?> ⭐</option>
            <?php endfor; ?>
        </select><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>