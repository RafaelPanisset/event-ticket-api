# Event Reservation API

A REST API for managing events and handling event reservations.

## Getting Started

### Prerequisites
- Composer
- Docker
- Docker Compose
- Git

### Installation & Setup

1. Clone the repository
```bash
git clone git@github.com:RafaelPanisset/event-ticket-api.git
cd event-ticket-api
```

2. Copy the environment file
```bash
cp .env.example .env
```

3. Install Composer
```bash
composer install
```

4. Start the Docker containers
```bash
docker-compose up -d
```

5. Run database migrations
```bash
docker-compose exec app php artisan migrate
```

6. Seed the database
```bash
docker-compose exec app php artisan db:seed
```


### API Access
The API will be available at `http://localhost:8000/`

Obs: If you need to change the port where the API runs, update the `APP_PORT` variable in the `.env` file.

### Testing
Run the tests:
```bash
docker-compose exec app php artisan test
```

## Headers
All requests must include the following headers:
```
Accept: application/json
Content-Type: application/json
```

## Database Structure

### Events Table
| Field         | Type      | Description                                    |
|---------------|-----------|------------------------------------------------|
| id            | bigint    | Primary key                                    |
| name          | string    | Name of the event                             |
| description   | text      |  Description of the event             |
| date          | datetime  | Date and time when the event takes place      |
| availability  | integer   | Number of available tickets                    |
| created_at    | timestamp |  creation timestamp                      |
| updated_at    | timestamp |  last update timestamp                   |

### Reservations Table
| Field           | Type      | Description                                    |
|-----------------|-----------|------------------------------------------------|
| id              | bigint    | Primary key                                    |
| event_id        | bigint    | Foreign key referencing events table          |
| customer_email  | string    | Email address of the customer                 |
| customer_name   | string    | Name of the customer                          |
| tickets_count   | integer   | Number of tickets reserved                    |
| created_at      | timestamp |  creation timestamp                      |
| updated_at      | timestamp |  last update timestamp                   |

## API Endpoints

### Events

#### GET /api/events
Retrieves a list of all events.

#### Query Parameters (Optional)
| Parameter    | Type    | Description                                    |
|-------------|---------|------------------------------------------------|
| future_only | boolean | When true, returns only upcoming events        |
| has_tickets | boolean | When true, returns events with available tickets|



**Response Example:**
```json
{
    "data": [
        {
            "name": "Obon",
            "description": "The festival is based on a legend about a Buddhist monk called Mogallana. The story goes that Mogallana could see into the afterlife and saved his deceased mother from going to hell by giving offerings to Buddhist monks. Having gained redemption for his mother, he danced in celebration, joined by others in a large circle. This dance is known as the Bon Odori dance.",
            "date": "2027-08-13 13:00:00",
            "availability": 10
        },
        {
            "name": "Carnival",
            "description": "This festival is known for being a time of great indulgence before Lent (which is a time stressing the opposite), with drinking, overeating, and various other activities of indulgence being performed. During Lent, dairy and animal products are eaten less, if at all, and individuals make a Lenten Sacrifice, thus giving up a certain object or activity of desire. On the final day of the season, Shrove Tuesday, many traditional Christians, such as Lutherans, Anglicans, and Roman Catholics, \"make a special point of self-examination, of considering what wrongs they need to repent, and what amendments of life or areas of spiritual growth they especially need to ask God's help in dealing with.\"",
            "date": "2013-03-03 10:00:00",
            "availability": 5
        },
        {
            "name": "Swiss Yodeling Festival",
            "description": "Natural yodeling exists all over the world, but especially in mountainous and inaccessible regions where the technique was used to communicate over extended distances. Although yodeling was probably used back in the Stone Age, the choir singing only developed in the 19th century.",
            "date": "2025-06-17 14:00:00",
            "availability": 1
        },
        {
            "name": "Tanabata Matsuri",
            "description": "This event celebrates the meeting of the deities Orihime and Hikoboshi (represented by the stars Vega and Altair respectively). According to legend, the Milky Way separates these lovers, and they are allowed to meet only once a year on the seventh day of the seventh lunar month of the lunisolar calendar.",
            "date": "2007-07-07 13:00:00",
            "availability": 200
        },
        {
            "name": "Sechseläuten",
            "description": "This Zurich Spring custom got its unusual name from the medieval custom of ringing a bell of the Grossmünster every evening at six o'clock to proclaim the end of the working day during the summer semester. Since it marked the beginning of springtime, the first ringing of the bell provided a good opportunity for a celebration.",
            "date": "2047-04-21 09:00:00",
            "availability": 0
        }
    ]
}
```

#### POST /api/events
Creates a new event.

**Request Body:**
```json
{
    "name": "Rock in Rio",
    "description": "One of the biggest music festivals in the world",
    "date": "2028-07-18 00:00:00",
    "availability": 100
}
```

#### GET /api/events/{id}
Retrieves a specific event by ID.

**Response Example:**
```json
{
    "name": "Rock in Rio",
    "description": "One of the biggest music festivals in the world",
    "date": "2028-07-18 00:00:00",
    "availability": 100
}
```

#### PUT /api/events/{id}
Updates a specific event.

**Response Example:**
```json
{
    "id": 6,
    "name": "Rock in Rio",
    "description": "One of the biggest music festivals in the world",
    "date": "2028-07-19 00:00:00",
    "availability": 50000,
}
```


#### DELETE /api/events/{id}
Deletes a specific event.

```json
{}
```
#### Response
- Success: Empty response with status code 204

**Decisions and possible improviments for the events requests**

I haven’t implemented it, but I believe adding a new column to the events table to track the initial total number of tickets available would be beneficial. Also, I’m currently retrieving all events at once, which could become costly and slow if the table grows too large. To address this, I wold also add pagination. I also added transactions and locking when updating or deleting events. This basically stops users from making reservations while the ticket numbers are being changed or while an event is being deleted.

### Reservations

#### POST /api/events/{event}/reserve
Creates a new reservation for an event.

**Request Body:**
```json
{
    "customer_email": "rafael@gmail.com",
    "customer_name": "Rafael Panisset",
    "tickets_count": 3
}
```

**Response Example:**
```json
{
    "data": {
        "id": 1,
        "event_id": 6,
        "customer_email": "rafael@gmail.com",
        "customer_name": "Rafael Panisset",
        "tickets_count": 3,
        "created_at": "2025-02-04T00:57:03.000000Z",
        "updated_at": "2025-02-04T00:57:03.000000Z"
    }
}
```

#### PUT /api/events/{event}/reserve
Updates an existing reservation.

**Request Body:**
```json
{
    "customer_email": "rafael@gmail.com",
    "tickets_count": 10
}
```

**Response Example:**
```json
{
    "data": {
        "id": 1,
        "event_id": 6,
        "customer_email": "rafael@gmail.com",
        "customer_name": "Rafael Panisset",
        "tickets_count": 10,
        "created_at": "2025-02-04T00:57:03.000000Z",
        "updated_at": "2025-02-04T01:01:22.000000Z"
    }
}
```

#### DELETE /api/events/{event}/reserve

**Request Body:**
```json
{
    "customer_email": "rafael@gmail.com"
}
```
**Response Example:**
```json
{
    "message": "Reservation cancelled"
}
```