# Tahap 1: Kompilasi aset Vue dengan Node.js
FROM node:22-alpine AS builder

WORKDIR /app

# Salin definisi dependensi dan pasang paket secara deterministik
COPY package*.json ./
RUN npm ci

# Salin seluruh kode frontend
COPY . ./

# Build aplikasi Vue untuk tahap produksi
ARG VITE_API_BASE_URL=/api
ENV VITE_API_BASE_URL=$VITE_API_BASE_URL
RUN npm run build

# Tahap 2: Penyajian aplikasi via server web Nginx
FROM nginx:1.27.4-alpine AS runner

# Salin berkas konfigurasi Nginx SPA
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Salin hasil kompilasi dari tahap builder (tanpa membawa node_modules)
COPY --from=builder /app/dist /usr/share/nginx/html

# Expose port HTTP
EXPOSE 80

# Jalankan Nginx di foreground
CMD ["nginx", "-g", "daemon off;"]
