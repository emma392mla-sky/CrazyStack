<?php
header('Content-Type: application/json');

// DATABASE CONNECTION ---------------------------
$host = "sql200.infinityfree.com";
$user = "if0_40320527";
$pass = "Tgcw6UDzTu4";
$db   = "if0_40320527_kwacha";

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // USERS -------------------------------------
    $users = $pdo->query("
        SELECT phone, balance, last_login 
        FROM users 
        ORDER BY id DESC
    ")->fetchAll();

    // WIN DATA ----------------------------------
    $winRaw = $pdo->query("
        SELECT phone, stake, won, target, time, date 
        FROM win
    ")->fetchAll();

    $wins = [];
    foreach ($winRaw as $row) {
        $safeTime = date("H:i:s", strtotime($row["time"]));

        $wins[] = [
            "phone" => $row["phone"],
            "stake" => floatval($row["stake"]),
            "won"   => floatval($row["won"]),
            "amount_lost" => 0,
            "target" => $row["target"],
            "target_landed" => null,
            "type" => "win",
            "time" => $safeTime,
            "date" => $row["date"]
        ];
    }

    // LOSE DATA ----------------------------------
    $loseRaw = $pdo->query("
        SELECT phone, amount_lost, target_landed, target_selected, time, date
        FROM lose
    ")->fetchAll();

    $losses = [];
    foreach ($loseRaw as $row) {
        $safeTime = date("H:i:s", strtotime($row["time"]));

        $losses[] = [
            "phone" => $row["phone"],
            "stake" => 0,
            "won"   => 0,
            "amount_lost" => floatval($row["amount_lost"]),
            "target" => $row["target_selected"],
            "target_landed" => $row["target_landed"],
            "type" => "lose",
            "time" => $safeTime,
            "date" => $row["date"]
        ];
    }

    // MERGE WIN & LOSE ----------------------------
    $merged = array_merge($wins, $losses);

    usort($merged, function($a, $b) {
        $dtA = DateTime::createFromFormat("Y-m-d H:i:s", $a["date"] . " " . $a["time"]);
        $dtB = DateTime::createFromFormat("Y-m-d H:i:s", $b["date"] . " " . $b["time"]);
        return $dtB <=> $dtA;
    });

    // TOTALS --------------------------------------

    // TOTAL LOSS = all stakes lost
    $totalLoss = array_sum(array_column($loseRaw, "amount_lost"));

    // TOTAL WIN = sum(won - stake)
    $totalWin = 0;
    foreach ($winRaw as $row) {
        $totalWin += floatval($row["won"]) - floatval($row["stake"]);
    }

    // OUTPUT DATA ---------------------------------
    echo json_encode([
        "users"  => $users,
        "win"    => $wins,
        "lose"   => $losses,
        "merged" => $merged,
        "totals" => [
            "totalWon"  => $totalWin,
            "totalLoss" => $totalLoss
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
