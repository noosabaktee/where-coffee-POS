# Entity Relationship Diagram

```mermaid
erDiagram
    OUTLETS ||--o{ USERS : employs
    OUTLETS ||--o{ PRODUCTS : owns
    OUTLETS ||--|| OUTLET_SETTINGS : configures
    OUTLETS ||--o{ EXPENSES : records
    OUTLETS ||--o{ TRANSACTIONS : processes

    CATEGORIES ||--o{ PRODUCTS : classifies
    USERS ||--o{ TRANSACTIONS : handles
    USERS ||--o{ EXPENSES : creates
    CUSTOMERS ||--o{ TRANSACTIONS : purchases

    TRANSACTIONS ||--|{ TRANSACTION_ITEMS : contains
    PRODUCTS ||--o{ TRANSACTION_ITEMS : snapshots
    PRODUCTS ||--o{ STOCK_MOVEMENTS : audited_by
    TRANSACTIONS ||--o{ STOCK_MOVEMENTS : causes
    CUSTOMERS ||--o{ LOYALTY_TRANSACTIONS : owns
    TRANSACTIONS ||--o{ LOYALTY_TRANSACTIONS : causes

    USERS ||--o{ MODEL_HAS_ROLES : assigned
    ROLES ||--o{ MODEL_HAS_ROLES : maps
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : contains
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : maps
```

## Keputusan desain

- Produk dan transaksi terisolasi berdasarkan `outlet_id`.
- Kategori serta member berlaku untuk seluruh jaringan outlet.
- `transaction_items` menyimpan snapshot nama, barcode, kategori, modal, dan harga. Laporan lama tetap valid walaupun produk kemudian berubah.
- `stock_movements` menjadi audit trail untuk stok awal, penjualan, dan adjustment.
- `loyalty_transactions` menyimpan histori earn/redeem dan saldo setelah transaksi.
- Setting pajak, service charge, branding, QRIS, serta rasio poin tersimpan per outlet.
