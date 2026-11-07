Weekly wave forecast & historical phenomenological record of the Balneário Camboriú coastline (blog & vlog).

Newsletter signup is shown whenever index.php is opened. In admin.php, use "Disparar newsletter" to compose and send a plain-text email to each subscriber, and "Inscritos" to view the email list.

After deploying newsletter database changes, log in to admin.php and select "Preparar/atualizar banco da newsletter" in the Inscritos tab. This creates or updates the subscriber table, repairs zero/invalid created_at values using the migration time, and fills unique 32-character unsub_token values for existing subscribers. An invalid legacy date cannot be recovered, so its replacement is the migration date. New subscription dates are explicitly saved in YYYY-MM-DD HH:MM:SS format using the America/Sao_Paulo timezone.

Newsletter delivery uses PHP's mail() function. The server must have mail delivery configured. NEWSLETTER_FROM_EMAIL can be set in the PHP environment; the default sender is contato@burysurf.com.