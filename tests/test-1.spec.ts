   import { test, expect } from '@playwright/test';

   test('login correcto', async ({ page }) => {
     await page.goto('http://localhost:3000/PROYECTO/public/paginas/Login.php');
     await page.locator('#username').fill('12345678');
     await page.locator('#clave').fill('123456');
     await page.getByRole('button', { name: 'Ingresar' }).click();
     await expect(page).not.toHaveURL(/Login\.php/);
   });