<?php
require_once 'conexao.php';

/*
CREATE TABLE despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('fixa','variavel') NOT NULL,
    data DATE NOT NULL
);
*/

function inserirDespesa($pdo, $descricao, $valor, $tipo, $data) {
    $stmt = $pdo->prepare("INSERT INTO despesas (descricao, valor, tipo, data) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$descricao, $valor, $tipo, $data]);
}
function listarDespesas($pdo) {
    return $pdo->query("SELECT * FROM despesas ORDER BY data DESC")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarDespesa($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM despesas WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarDespesa($pdo, $id, $descricao, $valor, $tipo, $data) {
    $stmt = $pdo->prepare("UPDATE despesas SET descricao=?, valor=?, tipo=?, data=? WHERE id=?");
    return $stmt->execute([$descricao, $valor, $tipo, $data, $id]);
}
function deletarDespesa($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM despesas WHERE id=?");
    return $stmt->execute([$id]);
}
function totalGasto($pdo) {
    $res = $pdo->query("SELECT SUM(valor) as total FROM despesas")->fetch();
    return $res['total'] ?? 0;
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirDespesa($pdo, $_POST['descricao'], $_POST['valor'], $_POST['tipo'], $_POST['data']);
        $mensagem = "Despesa adicionada!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarDespesa($pdo, $_POST['id'], $_POST['descricao'], $_POST['valor'], $_POST['tipo'], $_POST['data']);
        $mensagem = "Despesa atualizada!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarDespesa($pdo, $_GET['deletar']);
    $mensagem = "Despesa removida!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Controle de Despesas</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Despesas Pessoais</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Nova despesa</a>
<?php endif; ?>
<p><strong>Total gasto: R$ <?= number_format(totalGasto($pdo), 2, ',', '.') ?></strong></p>

<?php if($action === 'list'): 
    $despesas = listarDespesas($pdo);
    if(count($despesas) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Descrição</th><th>Valor</th><th>Tipo</th><th>Data</th><th>Ações</th></tr>
            <?php foreach($despesas as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['descricao']) ?></td>
                <td>R$ <?= number_format($d['valor'],2,',','.') ?></td>
                <td><?= $d['tipo'] ?></td>
                <td><?= date('d/m/Y', strtotime($d['data'])) ?></td>
                <td>
                    <a href="?action=edit&id=<?= $d['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $d['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhuma despesa cadastrada.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $despesa = $editando ? buscarDespesa($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Despesa' : 'Nova Despesa' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $despesa['id'] ?>"><?php endif; ?>
        Descrição: <input type="text" name="descricao" value="<?= $editando ? htmlspecialchars($despesa['descricao']) : '' ?>" required><br>
        Valor: <input type="number" step="0.01" name="valor" value="<?= $editando ? $despesa['valor'] : '' ?>" required><br>
        Tipo: 
        <select name="tipo">
            <option value="fixa" <?= $editando && $despesa['tipo']=='fixa' ? 'selected' : '' ?>>Fixa</option>
            <option value="variavel" <?= $editando && $despesa['tipo']=='variavel' ? 'selected' : '' ?>>Variável</option>
        </select><br>
        Data: <input type="date" name="data" value="<?= $editando ? $despesa['data'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>