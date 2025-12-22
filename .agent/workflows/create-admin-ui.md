---
description: สร้าง Admin UI สำหรับ eDonation
---

# Create Admin UI

## Tech Stack
- React + TypeScript
- Vite
- TailwindCSS 4
- shadcn/ui

## 1. สร้าง Vite Project
```bash
cd c:\xampp\htdocs\appdev\edonation\admin
npx -y create-vite@latest ./ --template react-ts
```

## 2. ติดตั้ง TailwindCSS
```bash
npm install tailwindcss @tailwindcss/vite
```

## 3. ตั้งค่า Vite Config
แก้ไข `vite.config.ts`:
```typescript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: '/edonation/admin/',
  server: {
    proxy: {
      '/api': 'http://localhost/appdev/edonation'
    }
  }
})
```

## 4. ติดตั้ง shadcn/ui
```bash
npx -y shadcn@latest init
```

## 5. เริ่ม Development
```bash
npm run dev
```

## API Base URL
- Development: `http://localhost/appdev/edonation/api`
- Production: `https://app.nurse.cmu.ac.th/edonation/api`
