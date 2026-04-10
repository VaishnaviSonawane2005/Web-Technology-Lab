package com.bookstore;

/**
 * Aggregated inventory statistics displayed on the dashboard.
 */
public class InventoryStats {

    private final int totalTitles;
    private final int totalStock;
    private final double totalInventoryValue;
    private final int lowStockTitles;

    public InventoryStats(int totalTitles, int totalStock, double totalInventoryValue, int lowStockTitles) {
        this.totalTitles = totalTitles;
        this.totalStock = totalStock;
        this.totalInventoryValue = totalInventoryValue;
        this.lowStockTitles = lowStockTitles;
    }

    public int getTotalTitles() {
        return totalTitles;
    }

    public int getTotalStock() {
        return totalStock;
    }

    public double getTotalInventoryValue() {
        return totalInventoryValue;
    }

    public int getLowStockTitles() {
        return lowStockTitles;
    }
}
