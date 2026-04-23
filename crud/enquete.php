<?php
require_once 'conexao.php';

/*
CREATE TABLE enquetes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta VARCHAR(255) NOT NULL
);

CREATE TABLE opcoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enquete_id INT,
    texto VARCHAR(100) NOT NULL,
    votos INT DEFAULT 0,
    FOREIGN KEY (enquete_id) REFERENCES enquetes(id) ON DELETE CASCADE
);
*/

function criarEnquete($pdo, $pergunta, $opcoesTexto) {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO enquetes (pergunta) VALUES (?)");
        $stmt->execute([$pergunta]);
        $enquete_id = $pdo->lastInsertId();
        $stmtOp = $pdo->prepare("INSERT INTO opcoes (enquete_id, texto) VALUES (?, ?)");
        foreach ($opcoesTexto as $texto) {
            if (trim($texto) != '')
                $stmtOp->execute([$enquete_id, $texto]);
        }
        $pdo->commit();
        return true;
    } catch(Exception $e) {
        $pdo->rollBack();
        return false;
    }
}
function listarEnquetes($pdo) {
    return $pdo->query("SELECT * FROM enquetes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
}
function getEnquete($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM enquetes WHERE id = ?");
    $stmt->execute([$id]);
    $enquete = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($enquete) {
        $stmt2 = $pdo->prepare("SELECT * FROM opcoes WHERE enquete_id = ?");
        $stmt2->execute([$id]);
        $enquete['opcoes'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    return $enquete;
}
function votar($pdo, $opcao_id) {
    $stmt = $pdo->prepare("UPDATE opcoes SET votos = votos + 1 WHERE id = ?");
    return $stmt->execute([$opcao_id]);
}
function deletarEnquete($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM enquetes WHERE id = ?");
    return $stmt->execute([$id]);
}

$mensagem = '';
$action = $_GET['action'] ?? 'list';

// Processar criação de enquete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar'])) {
    $opcoes = explode("\n", $_POST['opcoes']);
    $opcoes = array_map('trim', $opcoes);
    $opcoes = array_filter($opcoes);
    if (criarEnquete($pdo, $_POST['pergunta'], $opcoes))
        $mensagem = "Enquete criada!";
    else $mensagem = "Erro ao criar enquete.";
    $action = 'list';
}
// Votação via GET
if (isset($_GET['votar'])) {
    votar($pdo, $_GET['votar']);
    $mensagem = "Voto computado!";
    $action = 'list';
}
// Deletar enquete
if (isset($_GET['deletar'])) {
    deletarEnquete($pdo, $_GET['deletar']);
    $mensagem = "Enquete removida!";
    $action = 'list';
}
?>
<!DOCTYPE html>
<html>
<head><title>Sistema de Enquetes</title><style>body{font-family:Arial;margin:20px}</style></head>
<body>
<h1> Sistema de Enquetes</h1>
<?php if($mensagem): ?><p><strong><?= $mensagem ?></strong></p><?php endif; ?>
<a href="index.html">← Voltar ao menu</a> | 
<a href="?action=list">Enquetes</a> | 
<a href="?action=add">Criar nova enquete</a>

<?php if($action === 'list'): 
    $enquetes = listarEnquetes($pdo);
    if(count($enquetes) > 0): 
        foreach($enquetes as $e):
            $enq = getEnquete($pdo, $e['id']);
            $totalVotos = array_sum(array_column($enq['opcoes'], 'votos'));
        ?>
            <div style="border:1px solid #ccc; margin:15px 0; padding:10px;">
                <h3><?= htmlspecialchars($enq['pergunta']) ?></h3>
                <?php if($totalVotos == 0): ?>
                    <p>Nenhum voto ainda.</p>
                <?php else: ?>
                    <ul>
                    <?php foreach($enq['opcoes'] as $op): 
                        $percent = round(($op['votos'] / $totalVotos) * 100, 1);
                    ?>
                        <li><?= htmlspecialchars($op['texto']) ?> - <?= $op['votos'] ?> votos (<?= $percent ?>%)</li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <p>
                    <?php foreach($enq['opcoes'] as $op): ?>
                        <a href="?votar=<?= $op['id'] ?>" style="margin-right:10px">Votar em "<?= htmlspecialchars($op['texto']) ?>"</a>
                    <?php endforeach; ?>
                    <a href="?deletar=<?= $enq['id'] ?>" onclick="return confirm('Remover enquete?')">Excluir enquete</a>
                </p>
            </div>
        <?php endforeach;
    else: echo "<p>Nenhuma enquete criada.</p>"; endif;
elseif($action === 'add'): ?>
    <h2>Criar nova enquete</h2>
    <form method="POST">
        Pergunta: <input type="text" name="pergunta" size="50" required><br>
        Opções (uma por linha):<br>
        <textarea name="opcoes" rows="4" cols="50" required></textarea><br>
        <button type="submit" name="criar">Criar Enquete</button>
        <a href="?action=list">Cancelar</a>
    </form>
<?php endif; ?>
</body>
</html>