async page => {
  const resources = [
    'users',
    'roles',
    'permissions',
    'products',
    'brands',
    'product-categories',
    'orders',
    'payments',
    'refunds',
    'posts',
    'post-categories',
    'pages',
    'menus',
    'menu-items',
    'settings',
    'webhooks',
    'webhook-logs',
    'mail-logs',
  ];

  const hasError = async () => {
    const text = await page.locator('body').innerText().catch(() => '');
    return /Exception|ErrorException|SQLSTATE|Stack trace|Whoops|Server Error/i.test(text);
  };

  const results = [];

  for (const name of resources) {
    const base = `http://127.0.0.1:8000/admin/${name}`;
    const indexResponse = await page.goto(base, { waitUntil: 'domcontentloaded' });
    const item = {
      name,
      indexStatus: indexResponse?.status(),
      indexTitle: await page.title(),
      indexError: await hasError(),
      rowCount: await page.locator('tbody tr').count().catch(() => 0),
      createVisible: (await page.locator('a:has-text("Tạo mới")').count().catch(() => 0)) > 0,
    };

    if (await page.locator('a:has-text("Xem")').first().count()) {
      const [showResponse] = await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => null),
        page.locator('a:has-text("Xem")').first().click(),
      ]);

      item.showStatus = showResponse?.status();
      item.showTitle = await page.title();
      item.showError = await hasError();
      await page.goto(base, { waitUntil: 'domcontentloaded' });
    }

    if (await page.locator('a:has-text("Sửa")').first().count()) {
      const [editResponse] = await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded' }).catch(() => null),
        page.locator('a:has-text("Sửa")').first().click(),
      ]);

      item.editStatus = editResponse?.status();
      item.editTitle = await page.title();
      item.editError = await hasError();
    }

    results.push(item);
  }

  return results;
}
