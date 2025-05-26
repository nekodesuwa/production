function calculatePoints(turnCount) {
    const basePoints = 1000; // 基本ポイント
    if (turnCount === 0) {
        return basePoints; // ターン数が0の場合は最大ポイント
    }
    const points = basePoints / turnCount; // 反比例計算
    if (points < 100) {
        return 100; // 100未満の場合は100ポイント
    }
    return points;
}