# Task progress

## Chosen solutions
- I've started from implementing Redis queue to release file I/O limitation
- I choose the implementation of SSE to broadcast the events
- I refactored structure to DDD-like but need more refactorization and test coverage

## Need to be done
- Covering by tests
- Implementation of validation
- authentication to API and SSE

## How to use

### Running project
1. Run and build like in *'README.md'*
```bash
docker compose up --build -d
```

2. If you want to start the watcher run:
```bash
docker compose up watcher --build -d
```

3. You can test broadcasting by simple JS app:
```
"http://localhost:8000/client.html"
```
