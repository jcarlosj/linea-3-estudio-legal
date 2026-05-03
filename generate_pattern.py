import json

services = [
    ("Asuntos civiles y comerciales", "En el día a día de un negocio, los conflictos y las obligaciones contractuales son inevitables. Te ayudamos a gestionarlos antes de que se conviertan en un problema, estructurando tus relaciones jurídicas de forma clara y protegiéndote patrimonialmente de la manera más viable y eficiente para tu bolsillo."),
    ("Derecho corporativo", "Constituir, crecer y transformar una empresa tiene más implicaciones legales de las que parecen. Te acompañamos en cada etapa de tu negocio para que tomes decisiones seguras, evites riesgos innecesarios y construyas una estructura empresarial sólida sin sobrecostos ni sorpresas."),
    ("Tributario y aduanero", "Pagar tributos es una obligación, pero pagar de más no lo es. Te ayudamos a cumplir con tus obligaciones fiscales y aduaneras de manera eficiente, identificando las alternativas legales que mejor se adapten a tu negocio para que tu carga tributaria sea justa y manejable."),
    ("Derecho administrativo", "Relacionarse con el Estado puede ser complejo y desgastante. Te acompañamos en cada trámite, recurso o proceso ante entidades públicas para que no pierdas tiempo ni dinero en procedimientos innecesarios, y para que tus derechos siempre estén bien representados."),
    ("Regulación", "Operar en un sector regulado sin la asesoría correcta puede costarte más de lo que imaginas. Te ayudamos a entender y cumplir con las exigencias de las autoridades competentes de forma práctica y económicamente viable, convirtiendo la regulación en una ventaja en lugar de una carga."),
    ("Transporte", "El sector transporte tiene una regulación exigente y en constante cambio. Te acompañamos en el cumplimiento de tus obligaciones legales, la gestión de tus contratos y la representación ante las autoridades del sector, para que puedas enfocarte en tu operación sin preocupaciones legales de por medio."),
    ("Urbanístico", "Desarrollar un proyecto inmobiliario o de construcción sin el respaldo jurídico adecuado puede generar retrasos y costos que nadie quiere. Te asesoramos en licencias, permisos y cumplimiento normativo para que tu proyecto avance sin tropiezos y dentro del presupuesto."),
    ("Laboral", "Una mala gestión laboral puede salirle muy cara a cualquier empresa. Te ayudamos a estructurar tus relaciones de trabajo de forma correcta desde el principio, evitando conflictos, sanciones y procesos judiciales que afecten tu operación y tus finanzas."),
    ("Ambiental", "El incumplimiento de las obligaciones ambientales puede generar sanciones costosas y daños reputacionales difíciles de revertir. Te acompañamos en el cumplimiento de tus obligaciones, la obtención de permisos y la gestión de contingencias, para que operes con tranquilidad y responsabilidad."),
    ("Compliance (SAGRILAFT)", "No tener un programa de cumplimiento no es solo un riesgo legal — es un riesgo para la reputación y la continuidad de tu negocio. Te ayudamos a implementarlo de forma práctica y accesible, ajustado a las necesidades reales de tu empresa y sin complicaciones innecesarias."),
    ("Cannabis", "Colombia tiene uno de los marcos regulatorios más favorables del mundo para la industria del cannabis, pero navegarlo sin orientación puede ser costoso y frustrante. Te acompañamos en cada paso del proceso, desde la obtención de licencias hasta la estructuración de tu negocio, para que aproveches al máximo las oportunidades que ofrece este sector."),
    ("Zonas francas", "Operar bajo el régimen de zonas francas puede representar un ahorro significativo en materia tributaria y aduanera. Te asesoramos para que accedas a estos beneficios de forma correcta y los aproveches al máximo, con una estructura legal que soporte el crecimiento de tu negocio."),
    ("Extinción de dominio", "Enfrentar un proceso de extinción de dominio sin la representación adecuada puede tener consecuencias irreversibles. Te acompañamos con experiencia directa en este tipo de actuaciones, ofreciéndote una defensa técnica sólida y una orientación clara para proteger tu patrimonio en cada etapa del proceso.")
]

header = """<?php
/**
 * Title: Servicios Legales (Grid de 13 Tarjetas)
 * Slug: linea3-legal-child/servicios
 * Categories: antigravity-patterns
 * Description: Sistema de tarjetas para presentar servicios.
 */
?>
<!-- wp:group {"className":"l3-container-standard","layout":{"type":"constrained"}} -->
<div class="wp-block-group l3-container-standard">
    <!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}}} -->
    <h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50)">Nuestras Áreas de Práctica</h2>
    <!-- /wp:heading -->

    <!-- wp:group {"className":"l3-services-grid"} -->
    <div class="wp-block-group l3-services-grid">
"""

footer = """
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
"""

card_template = """        <!-- wp:cover {"url":"https://images.unsplash.com/photo-1505664173615-04b7e19fc3c6?auto=format&fit=crop&q=80&w=1000","dimRatio":80,"overlayColor":"base","className":"l3-service-card"} -->
        <div class="wp-block-cover l3-service-card"><span aria-hidden="true" class="wp-block-cover__background has-base-background-color has-background-dim-80 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="https://images.unsplash.com/photo-1505664173615-04b7e19fc3c6?auto=format&amp;fit=crop&amp;q=80&amp;w=1000" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
            <!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch","flexWrap":"nowrap"}} -->
            <div class="wp-block-group">
                <!-- wp:heading {"level":3} -->
                <h3 class="wp-block-heading">{title}</h3>
                <!-- /wp:heading -->
                <!-- wp:paragraph -->
                <p>{desc}</p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                <!-- wp:button {"className":"l3-btn-arrow"} -->
                <div class="wp-block-button l3-btn-arrow"><a class="wp-block-button__link wp-element-button">Conoce más</a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div></div>
        <!-- /wp:cover -->
"""

with open('/home/jcarlosj/Projects/Linea-3-Estudio-Legal/project/wordpress_data/wp-content/themes/linea3-legal-child/patterns/servicios.html', 'w', encoding='utf-8') as f:
    f.write(header)
    for title, desc in services:
        f.write(card_template.replace('{title}', title).replace('{desc}', desc))
    f.write(footer)

print("Pattern updated.")
