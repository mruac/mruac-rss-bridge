<?php
//https://rss-bridge.github.io/rss-bridge/Bridge_API/BridgeAbstract.html
class mruac_TestBridge extends BridgeAbstract
{
    const NAME = 'test Bridge';
    const URI = 'https://crouton.net';
    // const CACHE_TIMEOUT = 900; // 15mn
    const CACHE_TIMEOUT = 0;
    const MAINTAINER = 'mruac';
    const DESCRIPTION = 'test bridge playground';
    const PARAMETERS = [
        'parameter group' => [
            'text_var' => [
                'name' => 'input text tag',
                'type' => 'text',
                // 'required' => true
            ],
            'num_var' => [
                'name' => 'input number tag',
                'type' => 'number',
                'title' => 'hint on hover',
                'defaultValue' => 2

            ],
            'bool_var' => [
                'name' => 'input checkbox tag',
                'type' => 'checkbox'
            ]
        
        ]
    ];

    //collectData() is run before any other "getX()" is called. useful to gather info then create the dynamic titles / feed uris after.
    //eg. set private $profile and then get it in collectData(), then use $this->profile in getName, getIcon, getURI, etc.
    //$this->getItems() is then called after collectData() to fetch the contents of $this->items[]
    public function collectData()
    {
        // var_dump($this->getInput('text_var'));
        //https://github.com/dbox/html5-kitchen-sink
        //provided sources modified
        /* 
        Supported tags on Feedme android app:
        definition list (dt dl)
        b, strong
        blockquote
        del, s
        details
        figcaption
        figure
        heading tags (h1 ... h6)
        hr
        i, cite, dfn, em, address
        img
        ins, u
        kbd
        lists (ul, ol)
        mark
        p, nav
        pre, samp, code
        small
        sub
        sup
        table
        video
        wbr

        Unsupported tags:
        abbr
        article
        aside
        bdo
        data
        footer
        header
        hgroup
        iframe
        input
        math
        meter
        output
        progress
        q
        section
        select
        source
        summary
        svg
        time
        
        Notes:
        <progress> is continually loading
        <meter> uses the same style as <progress>, but is at 0%.
        no point in using <q> if it is used in conjunction with cite, but no formatting is applied.
        <i> <cite> <dfn> <address> use the same formatting
        <pre> <samp> <code> use the same stylised monospace formatting, making it distinct.
        <pre> are block elements.
        <samp> <code> are inline elements.
        <kbd> uses unstylised monospace formatting. making it look like someone just changed the font mid-paragraph
        <mark> highlites
        <del> <s> use the same formatting
        <ins> <u> use the same formatting
        <table> does not support borders

        */
        $HTMLKitchenSink = <<<EOD
  <header role="banner">
    <h1>HTML5 Kitchen Sink</h1>
    <small>Jump to: <a href="#headings">Headings</a> | <a href="#sections">Sections</a> | <a href="#phrasing">Phrasing</a> | <a href="#palpable">Palpable Content</a> | <a href="#embeds">Embeds</a> | <a href="#forms">Forms</a> | <a href="#tables">Tables</a> </small>    <br><br>
    <p>This section serves as the <code>header</code>.</p>
  </header>
  <hr>
  <main>
    <section id="headings">
      <h3><a href="#headings">#</a> Headings </h3>
      <p>Elements <code>h1</code>, <code>h2</code>, <code>h3</code>, <code>h4</code>,
        <code>h5</code>, <code>h6</code> make up the <em>heading content</em> category.
      </p>
      <hgroup>
        <h1>h1 I am most important.</h1>
        <h2>h2 Back in my quaint <a href='#'>garden</a></h2>
        <h3>h3 Jaunty <a href='#'>zinnias</a> vie with flaunting phlox.</h3>
        <h4>h4 Five or six big jet planes zoomed quickly by the new tower.</h4>
        <h5>h5 Expect skilled signwriters to use many jazzy, quaint old alphabets effectively.</h5>
        <h6>h6 Pack my box with five dozen liquor jugs.</h6>
      </hgroup>
              <h1>h1 I am most important.</h1>
        <h2>h2 Back in my quaint <a href='#'>garden</a></h2>
        <h3>h3 Jaunty <a href='#'>zinnias</a> vie with flaunting phlox.</h3>
        <h4>h4 Five or six big jet planes zoomed quickly by the new tower.</h4>
        <h5>h5 Expect skilled signwriters to use many jazzy, quaint old alphabets effectively.</h5>
        <h6>h6 Pack my box with five dozen liquor jugs.</h6>

      <footer>
        <p>See the <a target="_blank" href="https://www.w3.org/TR/html5/dom.html#heading-content">Heading Content spec.</a></p>
        <p>Note: these two paragraphs are contained in a <code>footer</code> element.
        </p>
      </footer>
    </section>

    <hr>
    <section id="sections">
      <header>
        <h3><a href="#sections">#</a> Sections</h3>
        <p>Elements <code>article</code>, <code>aside</code>, <code>nav</code>,
          <code>section</code> make up the <em>sectioning content</em> category.
        </p>
        <nav>
          These links are contained in a <code>nav</code> element.<br>
            <a href="#">Home</a>
            <a href="#">About</a>
            <a href="#">Contact</a>
        </nav>
      </header>
      <article>
        <p>This paragraph is nested inside an <code>article</code> element. It contains many different, sometimes useful, <a href="http://www.w3schools.com/tags/">HTML5 elements</a>. Of course there are classics like <em>emphasis</em>, <strong>strong</strong>,
          and <small>small</small> but there are many others as well. Hover the following text for abbreviation element: <abbr title="abbreviation">abbr</abbr>. You can define <del>deleted text</del> which often gets replaced with <ins>inserted</ins>          text.</p>
        <p>You can also use <kbd>keyboard text</kbd>, which sometimes is styled similarly to the <code>code</code> or <samp>samp</samp> elements. Even more specifically, there is an element just for <var>variables</var>. Not to be mistaken with block
          quotes below, the quote element lets you denote something as <q>quoted text</q>. Lastly don't forget the sub (H<sub>2</sub>O) and sup (E = MC<sup>2</sup>) elements.</p>
        <section>
          <p>This paragraph is contained in a <code>section</code> element of its parent <code>article</code> element.</p>
          <p>↓ The following paragraph has the <code>hidden</code> attribute and should not be displayed. ↓</p>
          <p hidden>→ You should not see this paragraph. ←</p>
          <p>↑ The previous paragraph should not be displayed. ↑</p>
        </section>
        Before article.<br>
        <article>
        Article introduction~
                <section>This is a section without any enclosing tag.</section>
                Closing of the article tag.
                        </article>
After article.

      </article>
      <aside>
        This is contained in an <code>aside</code> element.
      </aside>
      <footer>
        See the <a target="_blank" href="https://www.w3.org/TR/html5/dom.html#sectioning-content">Sectioning
Content spec.</a>
      </footer>
    </section>

    <hr>
    <section id="phrasing">
      <header>
        <h3><a href="#phrasing">#</a> Phrasing</h3>
        <p>Elements <code>abbr</code>, <code>b</code>, <code>bdi</code>,
          <code>bdo</code>, <code>br</code>, <code>cite</code>, <code>code</code>,
          <code>data</code>, <code>del</code>, <code>dfn</code>, <code>em</code>,
          <code>i</code>, <code>ins</code>, <code>kbd</code>, <code>mark</code>,
          <code>meter</code>, <code>progress</code>, <code>q</code>, <code>s</code>,
          <code>samp</code>, <code>small</code>, <code>span</code>, <code>strong</code>,
          <code>sub</code>, <code>sup</code>, <code>time</code>, <code>u</code>,
          <code>var</code>, <code>wbr</code>, and others make up the <em>phrasing content</em> category.
        </p>
      </header>
      <p><code>abbr</code>: Some vehicles meet the
        <abbr title="Super Ultra Low Emission Vehicle">SULEV</abbr> standard.<br>
        <code>br</code> was used to make this sentence start on a new line.</p>
      <p><code>bdi</code>: Some languages read right to left, <bdi lang="ar">مرحبا</bdi>.<br>
        <code>bdo</code>: <bdo dir="rtl">The normal direction has been
overridden.</bdo></p>
      <p><code>em</code> is used for <em>emphasis</em> and usually renders as italics, contrast that with <code>i</code> which is used for alternate voice or to offset from the normal (such as a phrase from a different language or taxonomic designation): <i>E. coli</i> can be bad. <code>strong</code> is used for <strong>importance or urgency</strong> and usually renders as bold, contrast that with <code>b</code> which is used to <b>draw attention</b> without the semantic meaning of  importance.</p>
      <p><code>cite</code>: In the words of <cite>Charles Bukowski</cite> — <q>An intellectual says a simple thing in a hard way. An artist says a hard thing in a simple way.</q></p>
      <p><code>data</code> can be used to specify <data value="2018-09-24T05:00-07:00">5 A.M.</data> that is machine-readable, but <code>time</code> is a better choice for specifying <time datetime="2018-09-24T05:00-07:00">5 A.M.</time> in a machine-readable format.
      </p>
      <p><code>del</code> can be <del datetime="2017-10-11T01:25-07:00">varily</del> used to mark deletions. <code>ins</code> marks <ins datetime="2007-12-19 00:00Z">insertions</ins>. <code>s</code>: similar to <code>del</code>, but used to mark content that is no longer relevant. <s>Windows XP version available.</s> <code>u</code>: a holdover with no real meaning that should be <u>removed</u>. <code>mark</code>: the HTML equivalent of the <mark>yellow highlighter</mark>. <code>span</code>: a
        <span>generic element</span> with no meaning by itself.</p>
      <p><code>dfn</code>: Foreign phrases add a certain <dfn lang="fr" title="French: indefinable quality">je ne sais quoi</dfn> to one's prose.
      </p>
      <p><code>q</code>: The W3C page <cite>About W3C</cite> says the W3C’s mission is <q cite="https://www.w3.org/Consortium/">To lead the World Wide Web to its full potential by developing protocols and guidelines that ensure long-term growth for the Web</q>.</p>
      <p><code>kbd</code> and <code>samp</code>: I did this:</p>
      <pre><samp>c:\&gt;<kbd>format c: /yes</kbd></samp></pre>
      <p>Is that bad? Press <kbd><kbd>Ctrl</kbd></kbd>+<kbd><kbd>F5</kbd></kbd> for a hard reload.</p>
      <p><code>var</code>: To log in, type <kbd>ssh <var>user</var>@example.com</kbd>, where <var>user</var> is your user ID.</p>
      <p><code>meter</code> and <code>progress</code>: Storage space usage:
        <meter value=6 max=8>6 blocks used (out of 8 total)</meter> Progress:
        <progress value="37" max=100>37%</progress></p>
      <p><code>sub</code> is used for subscripts: H<sub>2</sub>O. <code>sup</code> is used for superscripts: E = MC<sup>2</sup>. <code>small</code> is used for side comments: <q>I wrote this whole document. <small>[Editor's note: no he did not]</small></q>        <code>wbr</code>: used to specify where a word may break and it is super<wbr>cali<wbr>fragil<wbr>istic<wbr>expiali<wbr>do<wbr>cious.</p>
      <footer>
        <p>See the <a target="_blank" href="https://www.w3.org/TR/html5/dom.html#phrasing-content">Phrasing Content spec.</a></p>
      </footer>
    </section>

    <hr>
    <section id="palpable">
      <header>
        <h3><a href="#palpable">#</a> Palpable Content</h3>
        <p>Elements <code>a</code>, <code>address</code>, <code>blockquote</code>,
          <code>button</code>, <code>details</code>, <code>dl</code>, <code>fieldset</code>,
          <code>figure</code>, <code>form</code>, <code>input</code>, <code>label</code>,
          <code>map</code>, <code>ol</code>, <code>output</code>, <code>pre</code>,
          <code>select</code>, <code>table</code>, <code>textarea</code>,
          <code>ul</code>, and others make up the <em>palpable content</em> category.
        </p>
      </header>
      <p><code>a</code>: <a href="http://example.com">Example</a>.</p>
      <p><code>address</code>:</p>
      <address>1 Infinite Loop<br>
Cupertino, CA 95014<br>
United States
</address>
      <p><code>blockquote</code>:</p>
      <blockquote>
        <p>I quickly explained that many big jobs involve few hazards</p>
      </blockquote>
      <blockquote>
        <p>This is a multi-line blockquote with a cite reference. People think focus means saying yes to the thing you’ve got to focus on. But that’s not what it means at all. It means saying no to the hundred other good ideas that there are. You have to
          pick carefully. I’m actually as proud of the things we haven’t done as the things I have done. Innovation is saying no to 1,000 things.</p>
        <footer>Steve Jobs, <cite>Apple Worldwide Developers’ Conference, 1997</cite></footer>
      </blockquote>
      <blockquote>
        <b>Nested blockquote:</b> 80 days around the world, we’ll find a pot of gold just sitting where the rainbow’s ending. Time — we’ll fight against the time, and we’ll fly on the white wings of the wind.
        <blockquote>Knight Rider, a shadowy flight into the dangerous world of a man who does not exist. Michael Knight, a young loner on a crusade to champion the cause of the innocent, the helpless in a world of criminals who operate above the law.
</blockquote>
        80 days around the world, no we won’t say a word before the ship is really back. Round, round, all around the world. Round, all around the world. Round, all around the world. Round, all around the world.
      </blockquote>

      <p><code>details</code> and <code>summary</code>:</p>
      <details>
        <summary>Copying... <progress max="375505392" value="97543282"></progress> 25%
        </summary>
        <dl>
          <dt>Transfer rate:</dt>
          <dd>452KB/s</dd>
          <dt>Duration:</dt>
          <dd>01:16:27</dd>
          <dt>Color profile:</dt>
          <dd>SD (6-1-6)</dd>
          <dt>Dimensions:</dt>
          <dd>320×240</dd>
        </dl>
      </details>
      <p><code>dl</code>:</p>
      <dl>
        <dt>Definition List Title</dt>
        <dd>Definition list division.</dd>
        <dt>Kitchen Sink</dt>
        <dd>Used in expressions to describe work in which all conceivable (and some inconceivable) sources have been mined. In this case, a bunch of markup.</dd>
        <dt>aside</dt>
        <dd>Defines content aside from the page content</dd>
        <dt>blockquote</dt>
        <dd>Defines a section that is quoted from another source</dd>
      </dl>
      <p><code>figure</code>:</p>
      <figure>
        <img src="https://crouton.net/crouton.png">
        <figcaption>Figure 1: A pixelated picture of a single crouton from <a href="https://crouton.net">crouton.net</a>
        </figcaption>
      </figure>

      <br>
      <hr>
      <h4 id="forms"><a href="#forms">#</a> Forms</h4>
      <form>
        <p>
          <label for="example-input-email">Email address</label>
          <input type="email" id="example-input-email">
        </p>
        <p>
          <label for="example-input-number">Number</label>
          <input type="number" id="example-input-number">
        </p>
        <p>
          <label for="example-input-password">Password</label>
          <input type="password" id="example-input-password">
        </p>
        <p>
          <label for="example-input-search">Search</label>
          <input type="search" id="example-input-search">
        </p>
        <p>
          <label for="example-input-tel">Telephone number</label>
          <input type="tel" id="example-input-tel">
        </p>
        <p>
          <label for="example-input-text">Text</label>
          <input type="text" id="example-input-text">
        </p>
        <p>
          <label for="example-input-readonly">Read-only</label>
          <input type="text" id="example-input-readonly" readonly value="Can't touch this!">
        </p>
        <p>
          <label for="example-input-disabled">Disabled</label>
          <input type="text" id="example-input-disabled" disabled value="Not available">
        </p>
        <p>
          <label for="example-input-url">URL</label>
          <input type="url" id="example-input-url">
        </p>
        <p>
          <label for="example-input-color">Color</label>
          <input type="color" id="example-input-color">
        </p>
        <p>
          <label for="example-input-date">Date</label>
          <input type="date" id="example-input-date">
        </p>
        <p>
          <label for="example-input-date-time">Date / Time</label>
          <input type="datetime" id="example-input-date-time">
        </p>
        <p>
          <label for="example-input-date-time-local">Date / Time local</label>
          <input type="datetime-local" id="example-input-date-time-local">
        </p>
        <p>
          <label for="example-input-month">Month</label>
          <input type="month" id="example-input-month">
        </p>
        <p>
          <label for="example-input-week">Week</label>
          <input type="week" id="example-input-week">
        </p>
        <p>
          <label for="example-input-time">Time</label>
          <input type="time" id="example-input-time">
        </p>
        <p>
          <label for="example-input-file">File input</label>
          <input type="file" id="example-input-file">
        </p>
        <p>
          <label for="example-input-range">Range input</label>
          <input type="range" id="example-input-range" min="1" max="4" value="3">
        </p>
        <p>
          <label for="example-select1">Select</label>
          <select id="example-select1">
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
            <option>5</option>
          </select>
        </p>
        <p>
          <label for="example-select1a">Select with size</label>
          <select id="example-select1a" size="2">
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
            <option>5</option>
          </select>
        </p>
        <p>
          <label for="example-select2">Multiple select</label>
          <select multiple id="example-select2">
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
            <option>5</option>
          </select>
        </p>
        <p>
          <label for="example-optgroup">Select with optgroup: Favorite Car</label>
          <select id="example-optgroup">
            <optgroup label="Swedish Cars">
              <option>Volvo</option>
              <option>Saab</option>
            </optgroup>
            <optgroup label="German Cars">
              <option>Mercedes</option>
              <option>Audi</option>
            </optgroup>
          </select>
        </p>
        <p>
          <label for="example-optgroup2">Select with optgroup and size:Favorite Dish</label>
          <select id="example-optgroup2" size="2">
            <optgroup label="Vegetarian">
              <option>Green Salad</option>
              <option>French Fries</option>
            </optgroup>
            <optgroup label="Carnivorous">
              <option>Big Mac</option>
              <option>Roast Beef</option>
            </optgroup>
          </select>
        </p>
        <p>
          <label for="example-optgroup3">Multiple select with optgroup: Public transport</label>
          <select id="example-optgroup3" multiple>
            <optgroup label="Ground">
              <option>Train</option>
              <option>Bus</option>
            </optgroup>
            <optgroup label="Water">
              <option>Ship</option>
              <option>Submarine</option>
            </optgroup>
            <optgroup label="Air">
              <option>Plane</option>
              <option>Balloon</option>
            </optgroup>
          </select>
        </p>
        <p>
          <label for="example-textarea">Textarea</label>
          <textarea id="example-textarea" rows="3"></textarea>
        </p>
        <fieldset>
          <legend>I am legend</legend>
          <div>
            <input type="radio" name="option-radio" id="option-radio1" value="option1" checked>
            <label for="option-radio1">Option one is this and that&mdash;be sure to include why it's great</label>
          </div>
          <div>
            <input type="radio" name="option-radio" id="option-radio2" value="option2">
            <label for="option-radio2">Option two can be something else and selecting it will deselect option one</label>
          </div>
          <div>
            <input type="radio" name="option-radio" id="option-radio3" value="option3" disabled>
            <label for="option-radio3">Option three is disabled</label>
          </div>
        </fieldset>
        <fieldset>
          <legend>I am also legend</legend>
          <input type="checkbox" id="checkbox1">
          <label for="checkbox1">Check me out</label>
          <input type="checkbox" id="checkbox2">
          <label for="checkbox2">and/or check me out</label>
        </fieldset>
        <p>
          <button type="button" name="button">Button</button>
          <input type="button" name="input" value="Input Button">
          <input type="submit" name="submit" value="Input Submit">
          <button type="submit" name="submit2">Submit</button>
          <input type="reset" name="reset" value="Input Reset">
          <button type="reset" name="reset2">Reset</button>
          <button disabled>Cancel</button>
        </p>
      </form>

      <br>
      <hr>
      <p><code>output</code>:</p>
      <form onsubmit="return false" oninput="o.value= a.valueAsNumber + b.valueAsNumber">
        <input name=a type=number step=any> +
        <input name=b type=number step=any> =
        <output name=o for="a b"></output>
      </form>

      <br>
      <hr>
      <p><code>ul</code> and <code>ol</code>:</p>
      <ul>
        <li>Unordered List item one
          <ul>
            <li>Nested list item
              <ul>
                <li>Level 3, item one</li>
                <li>Level 3, item two</li>
                <li>Level 3, item three</li>
                <li>Level 3, item four</li>
              </ul>
            </li>
            <li>List item two</li>
            <li>List item three</li>
            <li>List item four</li>
          </ul>
        </li>
        <li>List item two</li>
        <li>List item three</li>
        <li>List item four</li>
      </ul>
      <ol>
        <li>List item one
          <ol>
            <li>List item one
              <ol>
                <li>List item one</li>
                <li>List item two</li>
                <li>List item three</li>
                <li>List item four</li>
              </ol>
            </li>
            <li>List item two</li>
            <li>List item three</li>
            <li>List item four</li>
          </ol>
        </li>
        <li>List item two</li>
        <li>List item three</li>
        <li>List item four</li>
      </ol>

      <br>
      <hr>
      <p><code>pre</code>:</p>
      <pre>
pre {
display: block;
padding: 7px;
background-color: #F5F5F5;
border: 1px solid #E1E1E8;
border-radius: 3px;
white-space: pre-wrap;
word-break: break-all;
font-family: Menlo, Monaco;
line-height: 160%;
}</pre>
      <pre><samp>You are in an open field west of a big white house with a boarded
front door.
There is a small mailbox here.

></samp> <kbd>open mailbox</kbd>

<samp>Opening the mailbox reveals:
A leaflet.

></samp></pre>

      <br>
      <hr>
      <h4 id="tables"><a href="#tables">#</a> Tables</h4>
      <table>
        <caption>Tables can have captions now.</caption>
        <tbody>
          <tr>
            <th>Person</th>
            <th>Number</th>
            <th>Third Column</th>
          </tr>
          <tr>
            <td>Someone Lastname</td>
            <td>900</td>
            <td>Nullam quis risus eget urna mollis ornare vel eu leo.</td>
          </tr>
          <tr>
            <td><a href="#">Person Name</a></td>
            <td>1200</td>
            <td>Vestibulum id ligula porta felis euismod semper. Donec ullamcorper nulla non metus auctor fringilla.</td>
          </tr>
          <tr>
            <td>Another Person</td>
            <td>1500</td>
            <td>Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor. Nullam id dolor id nibh ultricies vehicula ut id elit.</td>
          </tr>
          <tr>
            <td>Last One</td>
            <td>2800</td>
            <td>Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet fermentum.</td>
          </tr>
        </tbody>
      </table>
      <p id="table-summary">In the following table, characteristics are given in the second column, with the negative side in the left column and the positive side in the right column.</p>
      <table aria-describedby="table-summary">
        <caption>Characteristics with positive and negative sides</caption>
        <thead>
          <tr>
            <th id="n">Negative</th>
            <th>Characteristic</th>
            <th id="p">Positive</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td headers="n r1">Sad</td>
            <th id="r1">Mood</th>
            <td headers="p r1">Happy</td>
          </tr>
          <tr>
            <td headers="n r2">Failing</td>
            <th id="r2">Grade</th>
            <td headers="p r2">Passing</td>
          </tr>
        </tbody>
      </table>
      <table>
        <caption>Complex table with a <code>thead</code>, multiple <code>tbody</code> elements, and a <code>tfoot</code>.</caption>
        <thead>
          <tr>
            <th></th>
            <th>2008</th>
            <th>2007</th>
            <th>2006</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>Net sales</th>
            <td>$32,479</td>
            <td>$24,006</td>
            <td>$19,315</td>
          </tr>
          <tr>
            <th>Cost of sales</th>
            <td>21,334</td>
            <td>15,852</td>
            <td>13,717</td>
          </tr>
        </tbody>
        <tbody>
          <tr>
            <th>Gross margin</th>
            <td>$11,145</td>
            <td>$8,154</td>
            <td>$5,598</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th>Gross margin percentage</th>
            <td>34.3%</td>
            <td>34.0%</td>
            <td>29.0%</td>
          </tr>
        </tfoot>
      </table>
      <table>
        <caption>A Table with <code>col</code> and <code>colgroup</code> of proto-indo-europeans languages and words.</caption>
          <col />
        <colgroup>
          <col />
          <col />
          <col />
          <col />
        </colgroup>
        <colgroup>
          <col />
          <col />
          <col />
          <col />
        </colgroup>
        <thead>
          <tr>
            <td></td>
            <th colspan="4" id="germanic">Germanic</th>
            <th colspan="4" id="italic">Italic</th>
          </tr>
          <tr>
            <td></td>
            <th colspan="2" id="north-germanic" headers="germanic">North Germanic</th>
            <th colspan="2" id="west-germanic" headers="germanic">West Germanic</th>
            <th colspan="4" id="latin" headers="italic">Latin</th>
          </tr>
          <tr>
            <th id="modern-english">Modern English</th>
            <th id="old-norse" headers="north-germanic germanic">Old Norse</th>
            <th id="swedish" headers="north-germanic germanic">Swedish</th>
            <th id="old-english" headers="west-germanic germanic">Old English</th>
            <th id="old-dutch" headers="west-germanic germanic">Old Dutch</th>
            <th id="french" headers="latin italic">French</th>
            <th id="spanish" headers="latin italic">Spanish</th>
            <th id="portuguese" headers="latin italic">Portuguese</th>
            <th id="catalan" headers="latin italic">Catalan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th headers="modern-english" id="modern-english-no">No</th>
            <td headers="modern-english-no germanic north-germanic old-norse">Nei</td>
            <td headers="modern-english-no germanic north-germanic swedish">Nej</td>
            <td headers="modern-english-no germanic west-germanic old-english">Ná</td>
            <td headers="modern-english-no germanic west-germanic old-dutch">Nein</td>
            <td headers="modern-english-no latin italic french">Non</td>
            <td headers="modern-english-no latin italic spanish">No</td>
            <td headers="modern-english-no latin italic portuguese">não</td>
            <td headers="modern-english-no latin italic catalan">No</td>
          </tr>
          <tr>
            <th headers="modern-english" id="modern-english-yes">Yes</th>
            <td headers="modern-english-yes germanic north-germanic old-norse">já</td>
            <td headers="modern-english-yes germanic north-germanic swedish">Ja</td>
            <td headers="modern-english-yes germanic west-germanic old-english">Gea</td>
            <td headers="modern-english-yes germanic west-germanic old-dutch">Ja</td>
            <td headers="modern-english-yes latin italic french">Oui</td>
            <td headers="modern-english-yes latin italic spanish">Si</td>
            <td headers="modern-english-yes latin italic portuguese">sim</td>
            <td headers="modern-english-yes latin italic catalan">sí</td>
          </tr>
          <tr>
            <th headers="modern-english" id="modern-english-bear">Bear</th>
            <td headers="modern-english-bear germanic north-germanic old-norse">Bjorn</td>
            <td headers="modern-english-bear germanic north-germanic swedish">bjorn</td>
            <td headers="modern-english-bear germanic west-germanic old-english">bera</td>
            <td headers="modern-english-bear germanic west-germanic old-dutch">beer</td>
            <td headers="modern-english-bear latin italic french">Ours</td>
            <td headers="modern-english-bear latin italic spanish">Oso</td>
            <td headers="modern-english-bear latin italic portuguese">Urso</td>
            <td headers="modern-english-bear latin italic catalan">ós</td>
          </tr>
        </tbody>
      </table>
      <footer>
        <p>See the <a target="_blank" href="https://www.w3.org/TR/html5/dom.html#palpable-content">Palpable
Content spec.</a></p>
      </footer>
    </section>

    <hr>
    <section id="embeds">
      <header>
        <h3><a href="#embeds">#</a> Embeds</h3>
        <p>Elements <code>audio</code>, <code>canvas</code>, <code>embed</code>,
          <code>iframe</code>, <code>img</code>, <code>math</code>, <code>object</code>,
          <code>picture</code>, <code>svg</code>, <code>video</code> make up the <em>embedded content</em> category.</p>
      </header>
      <p><code>audio</code>: <audio controls src="https://upload.wikimedia.org/wikipedia/commons/c/c7/What_hath_God_wrought.ogg"></audio> By Cqdx [<a href="https://creativecommons.org/licenses/by-sa/3.0">CC BY-SA 3.0 </a>], <a href="https://commons.wikimedia.org/wiki/File:What_hath_God_wrought.ogg">from Wikimedia Commons</a>.</p>
      <p><code>iframe</code>: <iframe sandbox srcdoc="<h1>Sample Document</h1><p>This
is a sample content.</p>"></iframe></p>
      <p><code>img</code>: <img src="http://placekitten.com/150/150 " alt="a kitten"></p>
      <p><code>math</code>:</p>
      <math xmlns="http://www.w3.org/1998/Math/MathML">
<mtable>
<mtr>
<mtd>
<mtext>Quadratic Equation</mtext>
</mtd>
<mtd>
<mrow>
  <mi>x</mi>
  <mo>=</mo>
  <mfrac>
    <mrow>
      <mo>-</mo>
      <mi>b</mi>
      <mo>&#x00B1;</mo>
      <msqrt>
        <mrow>
          <msubsup>
            <mi>b</mi>
            <mrow></mrow>
            <mn>2</mn>
          </msubsup>
          <mo>-</mo>
          <mn>4</mn>
          <mi>a</mi>
          <mi>c</mi>
        </mrow>
      </msqrt>
    </mrow>
    <mrow>
      <mn>2</mn>
      <mi>a</mi>
    </mrow>
  </mfrac>
</mrow>
</mtd>
</mtr>
</mtable>
</math>
      <p><code>picture</code>:
        <picture>
  <source media="(min-width: 650px)" srcset="https://www.w3schools.com/htmL/img_food.jpg" alt="A variety of fruits, vegatables and leafy greens on display.">
  <source media="(min-width: 465px)" srcset="https://www.w3schools.com/htmL/img_car.jpg" alt="Alt text with line break. A portrait photo of a Porsche 911.
  The photo is framed in a way that the car is cut off on the left.">
  <img src="https://www.w3schools.com/htmL/img_girl.jpg" style="width:auto;" alt="Alt text with new line.\nRear photo of a person wearing a hooded coat and beanie.\nNo portion of the person is visible, apart from their clothing.">
        </picture>
      </p>
      <p><code>svg</code>: <svg role="presentation" width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19.199 24C19.199 13.467
10.533 4.8 0 4.8V0c13.165 0 24 10.835 24 24h-4.801zM3.291
17.415c1.814 0 3.293 1.479 3.293 3.295 0 1.813-1.485 3.29-3.301
3.29C1.47 24 0 22.526 0 20.71s1.475-3.294 3.291-3.295zM15.909
24h-4.665c0-6.169-5.075-11.245-11.244-11.245V8.09c8.727 0 15.909
7.184 15.909 15.91z" /></svg></p>
      <p><code>video</code>: <video controls src="https://sample-videos.com/video321/mp4/720/big_buck_bunny_720p_1mb.mp4" poster="https://upload.wikimedia.org/wikipedia/commons/d/d5/Big_Buck_Bunny_loves_Creative_Commons.png"></video></p>
      <footer>
        <p>See the <a target="_blank" href="https://www.w3.org/TR/html5/dom.html#embedded-content">Embedded Content spec.</a></p>
      </footer>
    </section>
  </main>
  <hr>
  <footer role="contentinfo">
    <p>This section serves as the <code>footer</code>.</p>
    <p>Find this document on <a href="https://github.com/dbox/html5-kitchen-sink">GitHub</a>.</p>
  </footer>
EOD;

        $this->addItem(
            [
            'uri' => 'https://crouton.net',
            'title' => 'le title3',
            'timestamp' => 'now',
            'author' => 'you',          
            'content' => $HTMLKitchenSink,
            'enclosures' => ['https://crouton.net/crouton.png'],
            'categories' => ['crouton', 'bread'],
            'uid' => '00006'             
            ]
        );

    }

    //function to generate rss feed name - recommended if dynamic depending on input vars
    public function getName()
    {
        return self::NAME;
    }

    //function to generate rss feed URI - recommended if dynamic depending on input vars
    public function getURI()
    {
        return self::URI;
    }

    //function to fetch and cache requests
    private function getData(string $url, bool $cache = false, bool $getJSON = false, array $httpHeaders = [], array $curlOptions = [])
    {
        // Debug::log($url);
        if ($getJSON) { //get JSON object
            if ($cache) {
                $data = $this->loadCacheValue($url, 86400); // 24 hours
                if (is_null($data)) {
                    $data = getContents($url, $httpHeaders, $curlOptions) or returnServerError("Could not load $url");
                    $this->saveCacheValue($url, $data);
                }
            } else {
                $data = getContents($url, $httpHeaders, $curlOptions) or returnServerError("Could not load $url");
            }
            return json_decode($data, true);
        } else { //get simpleHTMLDOM object
            if ($cache) {
                $html = getSimpleHTMLDOMCached($url, 86400); // 24 hours
            } else {
                $html = getSimpleHTMLDOM($url);
            }
            $html = defaultLinkTo($html, $url);
            return $html;
        }
    }

    /*
    */
        
    /**
    * function to add string to rss feed if not null (use null to veto an item)
    * $item = [
    * 'uri' => string             // URI to reach the subject ("https://...")
    * 'title' => string           // Title of the item
    * 'timestamp' => strtotime()  // Timestamp of the item in numeric or text format (compatible for strtotime())
    * 'author' => string          // Name of the author for this item
    * 'content' => html           // Content in HTML format
    * 'enclosures' => string[]    // Array of URIs to an attachments (pictures, files, etc...)
    * 'categories' => string[]    // Array of categories / tags / topics
    * 'uid' => string             // A unique ID to identify the current item
    * ]
     */
    private function addItem($item)
    {
        if (is_null($item)) {
            return;
        }

        if (is_array($item) || is_object($item)) {
            $this->items[] = $item;
        } else {
            returnServerError("Incorrectly parsed item. Check the code!\nType: " . gettype($item) . "\nprint_r(item:)\n" . print_r($item));
        }
    }
}
