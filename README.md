# Two version docker compose
- docker-compose.dev.yml
- docker-compose.prod.yml

# Commands
### -- Docker to build --
`docker-compose -f docker-compose.dev.yml up --build`

### -- Enter the console into the container  --
`docker-compose -f docker-compose.dev.yml exec app sh`

### -- View logs of a container --
`docker-compose -f docker-compose.dev.yml logs -f app`

# TODO List
- Reservation system ✅
- Notifications/Email ✅
- Switch Language (PL/ENG) ✅
- Refactor Docker configuration ⏳
- Improve UI ⏳
- Set up CI/CD pipeline ⏳
- Redis Cache ⏳
