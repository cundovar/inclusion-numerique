import { test, expect } from '@playwright/test';

test('inputSearch visible', async ({ page }) => {
  await page.goto('/');
  await expect(page.getByPlaceholder('Rechercher')).toBeVisible();
  await page.getByPlaceholder('Rechercher').fill('hello');
  await page.keyboard.press('Enter');
});
