<?php
require_once 'conexao.php';

/*
CREATE TABLE treinos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercicio VARCHAR(100) NOT NULL,
    series INT NOT NULL,
    repeticoes INT NOT NULL,
    carga DECIMAL(8,2) NOT NULL
);
*/

function inserirTreino($pdo, $exercicio, $series, $repeticoes, $carga) {
    $stmt = $pdo->prepare("INSERT INTO treinos (exercicio, series, repeticoes, carga) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$exercicio, $series, $repeticoes, $carga]);
}
function listarTreinos($pdo) {
    return $pdo->query("SELECT * FROM treinos ORDER BY exercicio")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarTreino($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM treinos WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarTreino($pdo, $id, $exercicio, $series, $repeticoes, $carga) {
    $stmt = $pdo->prepare("UPDATE treinos SET exercicio=?, series=?, repeticoes=?, carga=? WHERE id=?");
    return $stmt->execute([$exercicio, $series, $repeticoes, $carga, $id]);
}
function deletarTreino($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM treinos WHERE id=?");
    return $stmt->execute([$id]);
}
function volumeTotal($pdo) {
    $res = $pdo->query("SELECT SUM(series * repeticoes * carga) as vol FROM treinos")->fetch();
    return $res['vol'] ?? 0;
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirTreino($pdo, $_POST['exercicio'], $_POST['series'], $_POST['repeticoes'], $_POST['carga']);
        $mensagem = "Treino adicionado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarTreino($pdo, $_POST['id'], $_POST['exercicio'], $_POST['series'], $_POST['repeticoes'], $_POST['carga']);
        $mensagem = "Treino atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarTreino($pdo, $_GET['deletar']);
    $mensagem = "Treino removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Registro de Treinos</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1>Registro de Treinos</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Novo exercício</a>
<?php endif; ?>
<p><strong>Volume total (séries x repetições x carga): <?= number_format(volumeTotal($pdo), 2, ',', '.') ?></strong></p>

<?php if($action === 'list'): 
    $treinos = listarTreinos($pdo);
    if(count($treinos) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Exercício</th><th>Séries</th><th>Repetições</th><th>Carga (kg)</th><th>Volume</th><th>Ações</th></tr>
            <?php foreach($treinos as $t): 
                $vol = $t['series'] * $t['repeticoes'] * $t['carga'];
            ?>
            <tr>
                <td><?= htmlspecialchars($t['exercicio']) ?></td>
                <td><?= $t['series'] ?></td>
                <td><?= $t['repeticoes'] ?></td>
                <td><?= number_format($t['carga'],2,',','.') ?></td>
                <td><?= number_format($vol,2,',','.') ?></td>
                <td>
                    <a href="?action=edit&id=<?= $t['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $t['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum treino cadastrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $treino = $editando ? buscarTreino($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Exercício' : 'Novo Exercício' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $treino['id'] ?>"><?php endif; ?>
        Exercício: <input type="text" name="exercicio" value="<?= $editando ? htmlspecialchars($treino['exercicio']) : '' ?>" required><br>
        Séries: <input type="number" name="series" value="<?= $editando ? $treino['series'] : '' ?>" required><br>
        Repetições: <input type="number" name="repeticoes" value="<?= $editando ? $treino['repeticoes'] : '' ?>" required><br>
        Carga (kg): <input type="number" step="0.5" name="carga" value="<?= $editando ? $treino['carga'] : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>