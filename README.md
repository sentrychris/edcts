# FCOC - Fleet Carrier Services

### System

```json
{
    "id": 19874,
    "id64": 7268024133033,
    "name": "LHS 547",
    "coords": {
        "x": 0.5,
        "y": -38.21875,
        "z": 12.65625
    },
    "information": {
        "allegiance": "Empire",
        "government": "Feudal",
        "population": 234829,
        "security": "Medium",
        "economy": "Colony",
        "controlling_faction": {
            "name": "LFT 1 Noblement",
            "allegiance": "Empire"
        }
    },
    "departures": [
        {
            "id": 454,
            "title": "LHS 547 > Moortic | 19 July '23 01:54 UTC",
            "description": "Nam provident vero quia incidunt molestias quo labore. Aperiam tempore iste velit provident aperiam doloremque libero animi. Assumenda aut illum et sed deleniti optio reiciendis.\n\nNon cupiditate deserunt necessitatibus ut quae quibusdam. Laborum voluptas exercitationem corporis est. Et at et rem sunt et.",
            "destination": {
                "id": 13311,
                "id64": 10460596587,
                "name": "Moortic",
                "coords": {
                    "x": -73.21875,
                    "y": 49.96875,
                    "z": 12.78125
                },
                "updated_at": "2023-07-18 00:12:51"
            },
            "departs_at": "2023-07-19 01:54:00",
            "arrives_at": null,
            "status": {
                "cancelled": false,
                "boarding": true,
                "departed": false,
                "departed_at": false,
                "arrived": false,
                "arrived_at": false
            }
        }
    ],
    "arrivals": [],
    "updated_at": "2023-07-18 00:13:55"
}
```

### Carrier

```json
{
    "id": 898,
    "name": "Jennie Ferry",
    "identifier": "JDO-365",
    "commander": {
        "name": "Alexandria Swift"
    },
    "services": {
        "repair": false,
        "refuel": true,
        "armory": false,
        "shipyard": false,
        "outfitting": true,
        "cartographics": true
    },
    "schedule": [
        {
            "id": 982,
            "title": "Dhodia > CD-75 661 | 04 October '23 01:50 UTC",
            "description": "Laboriosam quam impedit sint sapiente temporibus alias. Et blanditiis aut illum rerum provident mollitia veritatis dicta.\n\nImpedit molestias aut consequatur est cupiditate est. Commodi libero repudiandae deleniti harum saepe natus explicabo. Est natus optio incidunt qui. Esse asperiores iure quia provident laudantium.",
            "departure": {
                "id": 6355,
                "id64": 5031654888170,
                "name": "Dhodia",
                "coords": {
                    "x": 6.65625,
                    "y": 25.625,
                    "z": 107.21875
                },
                "updated_at": "2023-07-18 00:11:46"
            },
            "destination": {
                "id": 18978,
                "id64": 2008132096730,
                "name": "CD-75 661",
                "coords": {
                    "x": 67.875,
                    "y": -21.5,
                    "z": 51.15625
                },
                "updated_at": "2023-07-18 00:13:46"
            },
            "departs_at": "2023-10-04 01:50:00",
            "arrives_at": null,
            "status": {
                "cancelled": true,
                "boarding": false,
                "departed": false,
                "departed_at": false,
                "arrived": false,
                "arrived_at": false
            }
        }
    ]
}
```

### Schedule

```json
{
    "id": 454,
    "title": "LHS 547 > Moortic | 19 July '23 01:54 UTC",
    "description": "Nam provident vero quia incidunt molestias quo labore. Aperiam tempore iste velit provident aperiam doloremque libero animi. Assumenda aut illum et sed deleniti optio reiciendis.\n\nNon cupiditate deserunt necessitatibus ut quae quibusdam. Laborum voluptas exercitationem corporis est. Et at et rem sunt et.",
    "departure": {
        "id": 19874,
        "id64": 7268024133033,
        "name": "LHS 547",
        "coords": {
            "x": 0.5,
            "y": -38.21875,
            "z": 12.65625
        },
        "information": {
            "allegiance": "Empire",
            "government": "Feudal",
            "population": 234829,
            "security": "Medium",
            "economy": "Colony",
            "controlling_faction": {
                "name": "LFT 1 Noblement",
                "allegiance": "Empire"
            }
        },
        "updated_at": "2023-07-18 00:13:55"
    },
    "destination": {
        "id": 13311,
        "id64": 10460596587,
        "name": "Moortic",
        "coords": {
            "x": -73.21875,
            "y": 49.96875,
            "z": 12.78125
        },
        "information": {
            "allegiance": "Independent",
            "government": "Democracy",
            "population": 296749,
            "security": "Medium",
            "economy": "Refinery",
            "controlling_faction": {
                "name": "The Fireflies",
                "allegiance": "Independent"
            }
        },
        "updated_at": "2023-07-18 00:12:51"
    },
    "departs_at": "2023-07-19 01:54:00",
    "arrives_at": null,
    "carrier": {
        "id": 1433,
        "name": "Christy Mitchell",
        "identifier": "YHL-923",
        "commander": {
            "name": "Rosetta Little"
        },
        "services": {
            "repair": true,
            "refuel": true,
            "armory": true,
            "shipyard": false,
            "outfitting": true,
            "cartographics": false
        }
    },
    "status": {
        "cancelled": false,
        "boarding": true,
        "departed": false,
        "departed_at": false,
        "arrived": false,
        "arrived_at": false
    }
}
```