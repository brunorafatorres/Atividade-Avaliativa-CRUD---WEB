<?php
require_once 'conexao.php';

/*
CREATE TABLE pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    especie VARCHAR(50) NOT NULL,
    idade INT NOT NULL,
    tutor VARCHAR(100) NOT NULL
);
*/

function inserirPet($pdo, $nome, $especie, $idade, $tutor) {
    $stmt = $pdo->prepare("INSERT INTO pets (nome, especie, idade, tutor) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nome, $especie, $idade, $tutor]);
}
function listarPets($pdo) {
    return $pdo->query("SELECT * FROM pets ORDER BY idade ASC")->fetchAll(PDO::FETCH_ASSOC);
}
function buscarPet($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function atualizarPet($pdo, $id, $nome, $especie, $idade, $tutor) {
    $stmt = $pdo->prepare("UPDATE pets SET nome=?, especie=?, idade=?, tutor=? WHERE id=?");
    return $stmt->execute([$nome, $especie, $idade, $tutor, $id]);
}
function deletarPet($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id=?");
    return $stmt->execute([$id]);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inserir'])) {
        inserirPet($pdo, $_POST['nome'], $_POST['especie'], $_POST['idade'], $_POST['tutor']);
        $mensagem = "Pet cadastrado!";
        $action = 'list';
    } elseif (isset($_POST['editar'])) {
        atualizarPet($pdo, $_POST['id'], $_POST['nome'], $_POST['especie'], $_POST['idade'], $_POST['tutor']);
        $mensagem = "Pet atualizado!";
        $action = 'list';
    }
}
if (isset($_GET['deletar'])) {
    deletarPet($pdo, $_GET['deletar']);
    $mensagem = "Pet removido!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Cadastro de Pets</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1> Cadastro de Pets</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<?php if($action === 'list'): ?>
    <a href="?action=add">Novo pet</a>
<?php endif; ?>

<?php if($action === 'list'): 
    $pets = listarPets($pdo);
    if(count($pets) > 0): ?>
        <table border="1" cellpadding="8">
            <tr><th>Nome</th><th>Espécie</th><th>Idade (anos)</th><th>Tutor</th><th>Ações</th></tr>
            <?php foreach($pets as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['especie']) ?></td>
                <td><?= $p['idade'] ?></td>
                <td><?= htmlspecialchars($p['tutor']) ?></td>
                <td>
                    <a href="?action=edit&id=<?= $p['id'] ?>">Editar</a> |
                    <a href="?deletar=<?= $p['id'] ?>" onclick="return confirm('Remover?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: echo "<p>Nenhum pet cadastrado.</p>"; endif;
elseif($action === 'add' || $action === 'edit'):
    $editando = ($action === 'edit');
    $pet = $editando ? buscarPet($pdo, $_GET['id']) : null;
?>
    <h2><?= $editando ? 'Editar Pet' : 'Novo Pet' ?></h2>
    <form method="POST">
        <?php if($editando): ?><input type="hidden" name="id" value="<?= $pet['id'] ?>"><?php endif; ?>
        Nome: <input type="text" name="nome" value="<?= $editando ? htmlspecialchars($pet['nome']) : '' ?>" required><br>
        Espécie: <input type="text" name="especie" value="<?= $editando ? htmlspecialchars($pet['especie']) : '' ?>" required><br>
        Idade: <input type="number" name="idade" value="<?= $editando ? $pet['idade'] : '' ?>" required><br>
        Tutor: <input type="text" name="tutor" value="<?= $editando ? htmlspecialchars($pet['tutor']) : '' ?>" required><br>
        <button type="submit" name="<?= $editando ? 'editar' : 'inserir' ?>">Salvar</button>
        <a href="?">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>