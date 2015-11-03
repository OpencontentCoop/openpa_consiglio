{set-block scope=root variable=subject}Pubblicazione del punto{/set-block}
Il punto è stato pubblicato {$punto.object.modified|datetime( 'custom', '%l %j %F %Y alle ore %H:%i' )|downcase()