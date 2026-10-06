# 🌿 MantraHeal CRM & Operations Suite — User Manual

**Version:** 2.5 Enterprise  
**Live Cloud Deployment:** [https://mantraheal-crm.onrender.com](https://mantraheal-crm.onrender.com)  
**System Architecture:** Laravel 11 • Docker • Apache • SQLite / MySQL Engine

---

## Table of Contents
1. [System Overview & Architecture](#1-system-overview--architecture)
2. [User Roles & Default Credentials](#2-user-roles--default-credentials)
3. [Logging In & Profile Management](#3-logging-in--profile-management)
4. [Leads Management & Telesales Pipeline](#4-leads-management--telesales-pipeline)
5. [In-App Softphone Dialer & Call Logging](#5-in-app-softphone-dialer--call-logging)
6. [Customer 360° Management](#6-customer-360-management)
7. [Orders, Invoicing & Fulfillment](#7-orders-invoicing--fulfillment)
8. [Courier Shipping, Dispatch & AWB Labels](#8-courier-shipping-dispatch--awb-labels)
9. [Returns, Refunds & RTO Handling](#9-returns-refunds--rto-handling)
10. [Inventory, Multi-Warehouse & FEFO Batch Expiry](#10-inventory-multi-warehouse--fefo-batch-expiry)
11. [Procurement, Purchase Orders & GRN](#11-procurement-purchase-orders--grn)
12. [Omnichannel Hub (Shopify, Meta Ads, WhatsApp)](#12-omnichannel-hub-shopify-meta-ads-whatsapp)
13. [Helpdesk & Customer Support](#13-helpdesk--customer-support)
14. [Executive Analytics & Reports](#14-executive-analytics--reports)
15. [System Administration & Security Settings](#15-system-administration--security-settings)

---

## 1. System Overview & Architecture

**MantraHeal CRM** is an end-to-end enterprise platform designed for Ayurvedic, Wellness, and E-Commerce direct-to-consumer (D2C) brands. It unifies:
- **Telesales & Lead Conversion**: Integrated softphone dialing, lead queues, audio recording, and automated follow-ups.
- **Omnichannel Ingestion**: Real-time webhook ingestion from Shopify stores, Meta Lead Ads, and WhatsApp Cloud API.
- **Logistics & Warehousing**: Multi-warehouse stock ledger with FEFO (First-Expired, First-Out) batch control, courier booking, and dispatch slips.
- **Financial Compliance**: Automated Indian GST tax calculation, reverse charge, HSN codes, and invoice generation in words.

---

## 2. User Roles & Default Credentials

The platform enforces Role-Based Access Control (RBAC). Access levels and pre-configured test credentials:

| Role | Default Email | Password | Primary Permissions |
|---|---|---|---|
| **Super Admin** | `admin@mantraheal.com` | `password123` | Complete root control over system settings, audit logs, financials, and staff. |
| **Operations Admin** | `operations@mantraheal.com` | `password123` | Daily order fulfillment, inventory adjustments, purchase orders, and supplier management. |
| **Sales Manager** | `vikram.manager@mantraheal.com` | `password123` | Lead assignment, agent call monitoring, sales reports, and campaign analytics. |
| **Sales Executive** | `rahul.sales@mantraheal.com` | `password123` | Telesales dialer, assigned lead pipeline, call logging, and customer conversion. |
| **Sales Executive** | `priya.sales@mantraheal.com` | `password123` | Direct customer follow-up, inbound consultation, and order creation. |
| **Inventory Manager** | `stock@mantraheal.com` | `password123` | Warehouse stock entry, GRN receipts, batch verification, and expiry tracking. |

> [!NOTE]
> On the login page, you can use the **1-Click Quick Preset Buttons** to instantly log in as any role without typing.

---

## 3. Logging In & Profile Management

1. Navigate to `https://mantraheal-crm.onrender.com/login` (or `http://127.0.0.1:8000/login` locally).
2. Enter your email and password, or click one of the role preset buttons.
3. Click **Sign In to CRM**.
4. To update your profile:
   - Click your avatar in the top-right header and select **My Profile**.
   - You can update your display name, mobile number, and password.

---

## 4. Leads Management & Telesales Pipeline

### Viewing & Filtering Leads
- Navigate to **Sales & Leads** > **Leads Pipeline**.
- Filter by status (**New**, **Contacted**, **Interested**, **Follow Up**, **Converted**, **Lost**) or campaign source (Facebook, Google Ads, Organic, Referral).
- Switch between **Table View** and **Kanban Board** to drag-and-drop leads across pipeline stages.

### Importing Leads from CSV
1. Click **Import CSV Leads** in the sidebar.
2. Download the CSV template by clicking **Download Sample Template**.
3. Fill in customer details (`name`, `mobile`, `email`, `city`, `source`, `product_interest`).
4. Upload the file. The CRM automatically deduplicates records by mobile number to prevent duplicate sales calls.

### Converting a Lead to an Order
1. Open a lead's detail page.
2. Click **Convert Lead**.
3. Select the Ayurvedic products requested by the client, enter shipping address, and click **Create Order**. The lead will be marked as **Converted** and linked to the new Customer 360 record.

---

## 5. In-App Softphone Dialer & Call Logging

MantraHeal includes a built-in virtual tele-calling softphone widget.

### Making Calls
1. Click the **Floating Green Phone Icon** at the bottom-right of any screen (or the **Dialer** button in the top bar).
2. Type or paste any 10-digit customer phone number.
3. The dialer will automatically search the CRM database and show matching contact details (name, city, and previous call history).
4. Click **Call** to begin the live call session. The timer begins tracking immediately.

### Call Disposition & Wrap-Up
1. When finished, click **End Call**.
2. Select the call outcome:
   - `Interested - Order Placed`
   - `Follow Up Scheduled`
   - `RNR (Ringing No Response)`
   - `Busy / Call Later`
   - `Not Interested / Junk`
3. Enter call summary notes.
4. If a follow-up is needed, set the date and time.
5. Click **Save Call Log**. The call duration, outcome, and audio recording link will be permanently logged under **Sales Calls Log** and **Call Recordings**.

---

## 6. Customer 360° Management

- Navigate to **Sales & Leads** > **Customers 360**.
- Search by customer name, mobile number, or email.
- Click any customer to open their complete history:
  - Total Lifetime Value (LTV) and total orders count.
  - Delivery addresses and pin code verification.
  - Complete timeline of sales calls, WhatsApp chats, and past invoices.
  - Medical health concerns, dosage notes, and customer tags (e.g. `VIP`, `Chronic Pain`, `Ayurvedic Detox`).

---

## 7. Orders, Invoicing & Fulfillment

### Creating an Order
1. Click **+ New Order** in the top navigation header.
2. Search and select an existing customer or type a new customer profile.
3. Add products from the catalog. Quantity, batch assignment, and pricing update dynamically.
4. Select payment method:
   - **Cash on Delivery (COD)**
   - **Prepaid (UPI / NetBanking / Razorpay)**
5. Click **Generate Order**.

### GST Tax Invoices
1. Navigate to **Order Lifecycle** > **All Orders**.
2. Select any order and click **Tax Invoice**.
3. The invoice includes:
   - Company GSTIN, PAN, and registered address.
   - CGST & SGST (Intrastate) or IGST (Interstate) breakdown based on the customer's state.
   - HSN codes and total amount in words.
   - Printable 1-click layout formatted for A4 standard invoice printers.

---

## 8. Courier Shipping, Dispatch & AWB Labels

### Booking Courier Dispatch
1. Go to **Order Lifecycle** > **Delivery & Dispatch**.
2. Pending orders appear ready for courier allocation.
3. Select the courier partner:
   - **Shiprocket**
   - **Delhivery**
   - **Bluedart**
   - **DTDC**
4. Click **Book Courier & Generate AWB**.
5. The system generates a valid tracking number and updates the order status to **Shipped**.

### Printing Shipping Labels
1. In the dispatch table, click **Print Label**.
2. The shipping slip includes:
   - Courier barcode & AWB tracking ID.
   - Return-to-origin (RTO) company warehouse address.
   - Customer shipping address with phone number.
   - COD collectible amount in large font for delivery agents.

---

## 9. Returns, Refunds & RTO Handling

### Return Request & Quality Check (QC)
1. Go to **Order Lifecycle** > **Returns & QC**.
2. Search the order number and select **Initiate Return**.
3. Choose the return reason (e.g., *Damaged Seal*, *Wrong Item*, *Adverse Reaction*).
4. Perform QC:
   - **Pass**: Restock back into inventory ledger.
   - **Fail**: Mark as scrap/damaged.

### RTO (Return to Origin) Management
1. Navigate to **RTO Management**.
2. View deliveries where the customer was unreachable or refused delivery.
3. Click **Mark Re-Attempt** to schedule a second courier delivery, or **Receive RTO** to restock items back into the assigned warehouse.

---

## 10. Inventory, Multi-Warehouse & FEFO Batch Expiry

### FEFO (First-Expired, First-Out) Policy
To comply with pharmaceutical and wellness manufacturing standards, products are dispatched strictly by **earliest expiry date first**.

### Managing Batches & Expiry Alerts
1. Navigate to **Products & Stock** > **Batches & FEFO**.
2. View batch numbers, manufacturing dates, and expiration countdowns.
3. Visit **Expiry Alerts** to see stock approaching 90, 60, and 30 days before expiration. The system highlights them with color-coded alerts so managers can discount or prioritize them.

### Stock Ledger Audit
- Navigate to **Stock Ledger**.
- Every single unit movement (Sale, Return, Purchase GRN, Damaged Scrap) is permanently logged with timestamp and user ID.

---

## 11. Procurement, Purchase Orders & GRN

1. **Suppliers**: Register raw herb and bottle suppliers under **Purchase & Supply** > **Suppliers**.
2. **Purchase Orders (PO)**: Generate formal procurement purchase orders with payment terms.
3. **Goods Received Note (GRN)**:
   - When shipments arrive at the warehouse, click **GRN (Goods Receipt)**.
   - Inspect quantities against the PO.
   - Enter newly assigned **Batch Number**, **Mfg Date**, and **Expiry Date**.
   - Confirming GRN instantly updates physical warehouse inventory counts.

---

## 12. Omnichannel Hub (Shopify, Meta Ads, WhatsApp)

### Shopify Sync Hub
- Navigate to **Communication** > **Shopify Sync Hub**.
- Click **Pull Orders** to sync recent online store checkouts into the CRM.
- Click **Push Fulfillment** to update Shopify orders with tracking numbers once courier AWB is booked in MantraHeal.

### Meta Ads Sync Hub
- Navigate to **Marketing & Ads** > **Meta Ads Sync Hub**.
- Live webhook integration captures leads from Facebook & Instagram Instant Forms within 2 seconds.
- Use the built-in **Simulation Engine** on the Settings page to test lead injection during staging.

### WhatsApp Cloud API
- Navigate to **Communication** > **WhatsApp Messenger**.
- Send automated order confirmation messages, courier dispatch notifications, and tele-consultation reminders using approved WhatsApp message templates.

---

## 13. Helpdesk & Customer Support

- Navigate to **Helpdesk Support** > **Tickets & Complaints**.
- Track tickets across **Open**, **In Progress**, and **Resolved** statuses.
- Assign tickets to wellness doctors or customer care executives.
- Internal private notes allow reps to coordinate before replying to the customer.

---

## 14. Executive Analytics & Reports

Access real-time executive analytics under **Analytics & Reports**:
- **Sales Reports**: Revenue trends, average order value (AOV), COD vs. Prepaid ratio.
- **Customer Reports**: Repeat purchase rates, customer retention cohorts.
- **Call Reports**: Tele-caller call volume, talk time, and conversion percentage.
- **Inventory Reports**: Out-of-stock risk, slow-moving items, and stock valuation.
- **RTO Reports**: Courier NDR (Non-Delivery Report) analysis and delivery failure rate by pin code/state.

---

## 15. System Administration & Security Settings

- **Employees & Roles**: Create staff accounts, set designations, and assign security roles.
- **Audit Logs**: Tamper-evident logging of every login, order edit, status update, and price change.
- **System Settings** (`/settings`):
  - Configure company legal entity, GSTIN, PAN, and corporate address.
  - Configure courier API credentials (Shiprocket / Delhivery).
  - Configure WhatsApp Business Cloud API (Phone Number ID & Access Token).
  - Run live connection diagnostic tests using the **Test Connection** button.

---

*© MantraHeal CRM. All rights reserved.*
