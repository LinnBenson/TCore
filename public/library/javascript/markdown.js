const renderer = new marked.Renderer();
renderer.codespan = ({ text }) => {
    return `<code class="span copy" data-clipboard-text="${md.show( text )}">${md.show( text )}</code>`;
};
renderer.code = ({ text }) => {
    return `
        <div class="showCode">
            <div class="showCodeHeader">
                <div></div>
                <div></div>
                <div></div>
                <i class="bi-copy copy" data-clipboard-text="${md.show( text )}"></i>
            </div>
            <div class="code">
                <pre class="code">${md.show( text )}</pre>
            </div>
        </div>
    `;
};
renderer.image = function({ href, text }) {
    return `
        <div class="showImg" onClick="unit.checkBig( \`${md.show( href )}\` )">
            <img src="${href}" alt="${md.show( text )}" />
            ${!empty( text ) ? `<span class="more">${text}</span>` : ''}
        </div>
    `;
};
marked.setOptions({ renderer });

window['md'] = {
    edit: ( text ) => {
        const parser = new DOMParser();
        const code = parser.parseFromString( text, "text/html").documentElement.textContent;
        return code;
    },
    show: ( text ) => {
        const parser = new DOMParser();
        const code = parser.parseFromString( text, "text/html").documentElement.textContent;
        return code.replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' ).replace( /'/g, '&#039;' );
    },
    to: ( text ) => {
        text = text.replace( "&gt; ", '> ' );
        return marked.parse( text );
    }
};