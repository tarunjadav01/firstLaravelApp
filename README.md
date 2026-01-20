Laravel Event Ingestion API
-----------------------------------------------
High-Level Flow (How the System Works)
- Client sends an API request with a Base64-encoded payload
- API validates the request and required fields
- Tenant is identified via request header
- Event is pushed to a queue job (async processing)
Queue job:
- Prevents duplicate events (idempotency)
- Creates or finds session
- Stores event in database
--------------------------------
Client
│
│ POST /api/events
│ (payload + X-Tenant-ID)
▼
API Route (routes/api.php)
▼
EventController@store
▼
ProcessEvent Job (Queue)
▼
Database (events, event_sessions)

-------------------------------------------------------------------------
- JWT authentication skipped for demo simplicity.JWTAuth facade tymon/jwt-auth package provide karta hai, jo tenant identification ke liye token se data extract karta hai.
-----------------------------------------------------------------
-Idempotency Is Implemented
-----------------------------------------------------------------
Idempotency ensures that the same event is not processed more than once.
Each event generates a unique event_hash using a deterministic combination of:
tenant_id
session_id
event_type
timestamp
event_hash = md5(tenant_id + session_id + event_type + timestamp)
--------------------------------------------------------------------------
- Assumptions & Trade-offs
-------------------------------------------------------------------------
Assumptions
--------------------------------------------------------------------------
Each tracking event is uniquely identifiable by its tenant, session, type, and timestamp
Events are write-heavy and read-light
Strict tenant isolation is required (no cross-tenant data access)
Slight delay in persistence is acceptable due to async processing
-------------------------------------------------------------------------
Trade-offs
-------------------------------------------------------------------------
Using a queue introduces eventual consistency (events are not immediately visible)
Hash-based idempotency increases storage but ensures data correctness
Database queue driver is simpler but less scalable than Redis or RabbitMQ
SQLite/MySQL is used for simplicity instead of distributed databases
-------------------------------------------------------------------------------
How to Run Locally
1️⃣ Clone the Repository
git clone <your-repo-url>
cd firstLaravelApp
2️⃣ Install Dependencies
composer install
3️⃣ Configure Environment
cp .env.example .env
php artisan key:generate
Update .env:
DB_DATABASE=firstlaravelapp
QUEUE_CONNECTION=database
4️⃣ Run Migrations
php artisan migrate
php artisan queue:table
php artisan migrate
5️⃣ Start the Application
php artisan serve
6️⃣ Start Queue Worker (Required)
php artisan queue:work
7️⃣ Send Event via Postman
POST
http://127.0.0.1:8000/api/events
--------------
Body (JSON):
{
  "payload": "eyJzZXNzaW9uX2lkIjoiUzEyMyIsImV2ZW50X3R5cGUiOiJwYWdlX3ZpZXciLCJ0aW1lc3RhbXAiOiIyMDI2LTAxLTAxVDEwOjAwOjAwWiJ9"
}
