# bKash Merchant Payment Integration

This integration allows customers to pay via bKash on the checkout page.

## Configuration

Update the `bkash/config.json` file with your bKash Merchant credentials:

```json
{
    "app_key": "YOUR_APP_KEY",
    "app_secret": "YOUR_APP_SECRET",
    "username": "YOUR_USERNAME",
    "password": "YOUR_PASSWORD",
    "base_url": "https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout"
}
```

For production, change the `base_url` to:
`https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout`

And update the bKash script URL in `checkout/index.php`:
Change `https://scripts.sandbox.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout-sandbox.js` 
to `https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js`

## Files Created
- `bkash/config.json`: Configuration for credentials.
- `bkash/token.php`: Handles token generation.
- `bkash/create.php`: Initiates payment creation with bKash.
- `bkash/execute.php`: Executes payment after user authorization.
- `bkash/bkash-helper.js`: Frontend helper to manage the bKash UI and API calls.

## Database Changes
The following columns were added to the `orders` table:
- `trx_id`: Stores the bKash Transaction ID.
- `payment_id`: Stores the bKash Payment ID.
