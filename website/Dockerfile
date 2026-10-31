# ----------------------------------------------------
# Stage 1: Development
# ----------------------------------------------------
FROM node:20-alpine AS development

WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .

ENV PORT=3000
ENV HOST=0.0.0.0
ENV WDS_SOCKET_PORT=8010
ENV CHOKIDAR_USEPOLLING=true

EXPOSE 3000

CMD ["npm", "start"]

# ----------------------------------------------------
# Stage 2: Production Build
# ----------------------------------------------------
FROM node:20-alpine AS builder

WORKDIR /app

COPY package*.json ./
RUN npm install --legacy-peer-deps

COPY . .

ARG REACT_APP_API_URL=http://localhost:8009/api/v1/storefront
ENV REACT_APP_API_URL=$REACT_APP_API_URL

RUN npm run build

# ----------------------------------------------------
# Stage 3: Production Nginx Server
# ----------------------------------------------------
FROM nginx:alpine AS production

COPY --from=builder /app/build /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

