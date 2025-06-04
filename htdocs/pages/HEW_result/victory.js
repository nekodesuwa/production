function calculatePoints(turnCount) {
    const basePoints = 1000; // 基本ポイント
    if (turnCount === 0) {
        return basePoints;
    }
    const points = basePoints / turnCount;
    if (points < 100) {
        return 100; // 最低100ポイント
    }
    return points;
}